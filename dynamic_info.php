<?php
	session_start();
	$mv_arr=$_SESSION['mv_arr'];
	header('Content-type: text/xml');
	echo "<?xml version='1.0' encoding='UTF-8'?>";
?>
<vxml version="2.1" application="MoviePhone.xml">
<form id="GetMovieInfo">
<?php 	
	$movie=$_GET["GlobalMovieName"];
	$id = $mv_arr[$movie];
// 	$id = 771374432;
	$json = file_get_contents("http://api.rottentomatoes.com/api/public/v1.0/movies/".$id.".json?apikey=rubsu9bxfydnncxhgrayh3pz");
	$json_decoded = json_decode($json, true);
	$lst_len = count($json_decoded["movies"]);
	//     $info_arr=array();
	$genre_arr = $json_decoded['genres'];
	$genre = htmlspecialchars(implode(', ', array_values($genre_arr)));
	$cast_arr = $json_decoded['abridged_cast'];
	$cast_lst = array();
	foreach ($cast_arr as $each){
		$cast_lst[] = htmlspecialchars($each['name']);
	}
	$cast = implode(', ', array_values($cast_lst));
	$mpaa = 'rated ' . $json_decoded['mpaa_rating'];
	$runtime = $json_decoded['runtime'] . ' minutes';
	$synopsis = htmlspecialchars($json_decoded['synopsis']);
	$studio = $json_decoded['studio'];
	$director = $json_decoded['abridged_directors'][0]['name'];
	$cr_score = 'a ' . $json_decoded['ratings']['critics_rating'] . ' score of ' . $json_decoded['ratings']['critics_score'];
	$au_score =  'a ' . $json_decoded['ratings']['audience_rating'] . ' score of ' . $json_decoded['ratings']['audience_score'];
	$info_arr = array('genre'=>$genre,'m p eh eh rating'=>$mpaa,'run time'=>$runtime,'critic score'=>$cr_score,'audience score'=>$au_score,'director'=>$director,'synopsis'=>$synopsis,'studio'=>$studio,'cast'=>$cast);
?>
  <field name="MovieInfo">
   <prompt>
    <break /> Tell me what you would like to know, or ask to hear your options. <break />
   </prompt>
 
   <grammar xml:lang="en-US" root = "InfoGrammar">
    <rule id="InfoGrammar" scope="public">
	<one-of>
		<item><ruleref special="GARBAGE"/></item>
		<item><ruleref special="NULL"/></item>
	</one-of>
     <one-of>
      <item>options<tag>out.MovieInfo = 'options';</tag></item>
      <?php 
		    	foreach ($info_arr as $option => $info){
		    		echo "<item>".$option."<tag>out.MovieInfo=' the ".$option." is: ".$info." ';</tag></item>";
				} 
		?>
     </one-of>
	 <one-of>
		<item><ruleref special="GARBAGE"/></item>
		<item><ruleref special="NULL"/></item>
	</one-of>
    </rule>
   </grammar>
   <filled namelist="MovieInfo">
	<assign name="GlobalMovieInfo" expr="MovieInfo" />
	  <if cond="MovieInfo == 'options'">
		<prompt bargein="true">
			The available options are:<break/>
			<?php 
			    	foreach ($info_arr as $option => $info){
			    		echo $option."<break time='10ms'/>";
					} 
				?>
		</prompt>
		<goto next="#GetMovieInfo" /> 
 		 <else /> 
	 	  	<value expr="GlobalMovieInfo"/>
	  		<goto next="#GetContinue" /> 
			</if> 
		</filled>
  </field>
</form>


<form id="GetContinue">
 
  <field name="Continue">
   <prompt>
	<break/> Would you like to hear more about the same movie, a different movie, or quit? <break />
   </prompt>
 
   <!-- Grammar for available responses for either City, State name.-->
   <grammar xml:lang="en-US" root = "ContinueGrammar">
    <rule id="ContinueGrammar" scope="public">
	<one-of>
		<item><ruleref special="GARBAGE"/></item>
		<item><ruleref special="NULL"/></item>
	</one-of>
     <one-of>
     <item>
     <one-of>
      <item>same movie</item>
     </one-of>
     <tag>out.Continue ="the same movie";</tag>
     </item>
     <item>
     <one-of>
      <item>different movie</item>
     </one-of>
     <tag>out.Continue ="a different movie";</tag>
     </item>
	 <item>
     <one-of>
      <item>quit</item>
     </one-of>
     <tag>out.Continue ="quit";</tag>
     </item>
     </one-of>
	 <one-of>
		<item><ruleref special="GARBAGE"/></item>
		<item><ruleref special="NULL"/></item>
	</one-of>
    </rule>
   </grammar>
  </field>
 
 <filled namelist="Continue">
  <if cond="Continue == 'the same movie'">
  <prompt>
	You'd like to know about <value expr="Continue" /> <break /> 
	</prompt>
	 <goto next="#GetMovieInfo" />
  <elseif cond="Continue == 'a different movie'" />
  <prompt>
	You'd like to know about <value expr="Continue" /> <break />
	</prompt>
	<goto next="dynamic_lst.php#GetMovieName" />
	<elseif cond="Continue == 'quit'" />
	<prompt>
	Thank you for calling rotten tomatoes movie hotline. <break /> Happy watching!
	</prompt>
	</if>
</filled>
</form>
</vxml>