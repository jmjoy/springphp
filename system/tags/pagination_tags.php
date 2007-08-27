<?php
/*
 * Copyright 2007 the original author or authors.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

 /**
 * type_name
 *
 * @package		Inside ASI
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @since		Version 1.0
 * @filesource
 */

 //TODO: pagination helpers
 
 function pagination_results($pagination, $options = array()) {
 	global $request;
 
 	$total = $pagination->getTotalItems();
 	$currentPage = $pagination->getPage();
 	$itemsPerPage = $pagination->getItemsPerPage();
 	
 	$totalPages = ceil($total / $itemsPerPage);
 	$content = '';
 	if($totalPages > 1) {
			
		$content .= '<div class="page-prev-but">';
		if($currentPage > 1)
			$content .= '<a href="'.add_query_arg('page', ($currentPage-1).','.($itemsPerPage), $request->getCurrentURL()).'" title="Previous">Prev</a>';
		else
			$content .= '<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>';
		$content .= '</div>'."\n";
			
		$startPage = 1;
		if($currentPage >= 6 && ($totalPages) >= 9)
			$startPage = ($totalPages-4<$currentPage)?$totalPages-8:$currentPage-4;
		
		$endPage = ($startPage+8>=$totalPages)?$totalPages:($startPage+8);
			
		for($i = $startPage; $i <= $endPage; ++$i) {
		
			if($i == $currentPage) $content .=  '<span>'.$i.'</span>'."\n";
			else{
				$startCount = $i==1?'1':(($i-1)*$itemsPerPage)+1;
				$endCount = $i*$itemsPerPage>$total?$total:$i*$itemsPerPage;
				$content .= '<a href="'.add_query_arg('page', ($i).','.($itemsPerPage), $request->getCurrentURL()).'" title="Results '.$startCount." - ".$endCount.'">'.$i.'</a>'."\n";
			}
		
		}
			
		$content .= '<div class="page-next-but">';
		if($currentPage < ($totalPages))
			$content .= '<a href="'.add_query_arg('page', ($currentPage+1).','.($itemsPerPage), $request->getCurrentURL()).'" title="Next">Next</a>';
		$content .= '</div>'."\n";
		
		return content_tag('div', $content, $options);
 	
 	}
 	return $content;
 }
 
 ?>
