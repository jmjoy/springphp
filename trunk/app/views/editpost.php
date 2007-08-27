<html>

	<head>
		<title>Spring PHP Demo</title>
	</head>

	<body>
		
		<? if($command->id != null) { ?>
			<h1>Edit Post: <?=$command->title;?></h1>
		<? } else { ?>
			<h1>Create New Post</h1>
		<? } ?>

		
		<?=form()?>
		
			<?=form_errors('*')?>

			<div>
				<?=label('title', 'Title: ')?>
				<?=input('title')?>
			</div>
			
			<div>
				<?=label('content', 'Content: ')?>
				<?=textarea('content')?>
			</div>
			
			<div>
				<?=submit_tag();?>
			</div>
			
		<?=_form()?>
	
		<hr/>
		
		<p><a href="<?=system_url('/')?>">Back to Blog</a></p>
		
		<p>Elapsed Time: {elapsed_time}</p>
	
	
	</body>
</html>