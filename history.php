<?php header("Content-Type:text/plain"); ?>
<?php
$updir = "data/";

require_once('conf/default_config.php');

try{
	if(!$conf['git']){
		echo "failed\n";
		echo "Git is not installed; history support requires Git installed on the server.";
		return;
	}

	if(!isset($_GET['fname']) || $_GET['fname'] === '')
		throw new Exception('fname is not specified.');
	$fname = $_GET['fname'];
	if(isset($_GET['hash']))
		$hash = $_GET['hash'];
	else
		$hash = "";

	require_once('gitphp/Git.php');
	Git::set_bin($conf['git_path']);

	$repo = new GitRepo('data');
	if($hash !== ""){
		$tree = $repo->run('ls-tree ' . $hash . ' "' . $fname . '"');
		$blobs = explode(" ", $tree);

		// Error checking
		if(!isset($blobs[1]) || $blobs[1] !== 'blob' || !isset($blobs[2]) || strlen($blobs[2]) < 40)
			throw new Exception('unexpected response from "git ls-tree": ' . $tree);

		$response = $repo->run('cat-file -p ' . substr($blobs[2], 0, 40));
		echo "succeeded\n";
		echo $response;
	}
	else{
		$response = $repo->run('log --format="%h %ai" "' . $fname . '"');
		echo "succeeded\n";
		echo $response;
	}
}
catch(Exception $e){
	echo "failed\n";
	echo $e->getMessage();
}
?>