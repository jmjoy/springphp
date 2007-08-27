<html>

	<head>
		<title>Spring PHP Demo</title>
	</head>

	<body>
		
		<h1>My Blog</h1>
		
		<? foreach($posts as $post) { ?>
			
			<h3><?=$post->title;?></h3>
			
			<p><?=$post->content;?></p>
			
			<p><a href="<?=system_url('/editpost/'.$post->id)?>">Edit</a> <a href="<?=system_url('/deletepost/'.$post->id)?>">Delete</a></p>
			
			<hr/>
		
		<? } ?>
		
		<p><a href="<?=system_url('/createpost')?>">Create New Post</a></p>
		
		<p>Elapsed Time: {elapsed_time}</p>
	
	</body>
</html>