<?php
/* pixel-dusche.de */

global $hl,$uniqueID, $button, $imgSize, $imgClass, $template4count, $templateTotal;
global $fileID, $lastUsedTemplateID, $anker, $content_right, $grIMG, $personenDIV, $ext_link;

$fileID = basename(__FILE__, '.php');
$lastUsedTemplateID = $fileID;

$edit_mode_class = 'container_edit ';

$template = '
<section class="section_bot">
	<div class="container h-100">
		<div class="row g-0 h-100 align-items-center">
			<div class="text1 z-1 position-relative text-white fw-bold text-center text-uppercase">
			<a href="'.$ext_link.'">#cont#</a>
			</div>
		</div>
		<a href="'.$ext_link.'"><img class="image position-absolute w-100 h-100 top-0 start-0" src="'.$grIMG.'" alt=""></a>
	</div>
</section>
';
  
$hl = '';
$button = '';
$content_right = '';
