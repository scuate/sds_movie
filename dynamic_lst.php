<?php
	session_start();
	header('Content-type: text/xml');
	echo "<?xml version='1.0' encoding='UTF-8'?>";
	$json = file_get_contents('http://api.rottentomatoes.com/api/public/v1.0/lists/movies/in_theaters.json?apikey=rubsu9bxfydnncxhgrayh3pz');
	$json_decoded = json_decode($json, true);
	//     $lst_len = count($json_decoded["movies"]);
	$lst_len = 8;
	$mv_arr=array();
	for ($i=0; $i < $lst_len; $i++){
		$movie = $json_decoded['movies'][$i]['title'];
		$id = $json_decoded['movies'][$i]['id'];
		$mv_arr[$movie] = $id;
	}
	$_SESSION['mv_arr'] = $mv_arr;
?>
	
<vxml version="2.1" application="MoviePhone.xml">
  <form id="GetMovieName">
    <field name="MovieName">
	    <prompt bargein="true">
			 Say the name of the movie you would like to know about, or ask to hear a list of current movies.
		</prompt>
		<grammar xml:lang="en-US" type="application/grammar-xml">
			<rule id="m_name" scope="public">
				<one-of>
					<item><ruleref special="GARBAGE"/></item>
					<item><ruleref special="NULL"/></item>
				</one-of>
				<one-of>
					<item>current movies<tag>out.MovieName='current movies'</tag></item>
			<?php 
		    	foreach ($mv_arr as $movie => $id){
		    		echo "<item>".$movie."<tag>out.MovieName='".$movie."'</tag></item>";
				} 
			?>
				</one-of>
				<one-of>
					<item><ruleref special="GARBAGE"/></item>
					<item><ruleref special="NULL"/></item>
				</one-of>
			</rule>	
	    </grammar>
	    <filled namelist="MovieName">
		<assign name="GlobalMovieName" expr="MovieName" />
		<if cond="MovieName == 'current movies'">
			 <prompt bargein="true">
				The current movies in theater are:
				<?php 
			    	foreach ($mv_arr as $movie => $id){
			    		echo $movie."<break time='10ms'/>";
					} 
				?>
			 </prompt>
			 <goto next="#GetMovieName" />
		<else/>
			 Ok, you would like to know about <value expr="GlobalMovieName" /> <break/>
			 <submit next="dynamic_info.php#GetMovieInfo" namelist="GlobalMovieName" method="get"/>
		</if>
		</filled>
	 </field>
  </form>
</vxml>