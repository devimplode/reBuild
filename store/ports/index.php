<!DOCTYPE>
<html>
	<head>
		<title>It Works!</title>
	</head>
	<body>
		<h1>It works!</h1>
<?php
	$url="mysqli://root@localhost:3306/test";
	$db=system::SM()->get($url);
	if($db && $db->test && $db->test->temp){
		$data=$db->test->temp->select()->limit(3)->query()->getAssoc();
		echo("<table>\n<tbody>\n<tr>\n");
		foreach($data[0] as $k=>$v){
			echo("<td>".$k."</td>\n");
		}
		echo("</tr>\n");
		foreach($data as $id=>$arr){
			echo("<tr>\n");
			foreach($arr as $k=>$v){
				echo("<td>".$v."</td>\n");
			}
			echo("</tr>\n");
		}
		echo("</tbody>\n</table>");
	}
?>
		<img src="//<?php echo(system::RM()->request('host'))?>/yay.png" alt="Yay!" />
	</body>
</html>