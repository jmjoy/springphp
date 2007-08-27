<?php

 class PostValidator extends Validator {
 
	function supports($class) {
		if(is_subclass($class, 'Post')) return true;
	}

	function validate(&$target, &$bindingResult) {
		ValidationUtils::rejectIfEmptyOrWhitespace($bindingResult, "title", "post.field.required");
		ValidationUtils::rejectIfEmptyOrWhitespace($bindingResult, "content", "post.field.required");

	}

}
 ?>

