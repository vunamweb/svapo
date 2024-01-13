			
			
<?php
/* pixel-dusche.de */

global $fileID, $lastUsedTemplateID, $Slider, $title, $imageFolder, $button_links;
global $cid, $uniqueID, $morpheus, $lan, $ipad, $buttons, $styles, $ankerLink, $farbe, $tabstand, $tabstand_bottom, $class, $grIMG, $templateIsClosed, $mobileIMG;
global $shopurl, $is_logged;

if(!$Slider) $Slider = 1;
else $Slider++;

$fileID = basename(__FILE__, '.php');
$lastUsedTemplateID = $fileID;

$edit_mode_class = 'container_edit ';

if($tref == 1 || !$tref) {
	$template = '
<section class="hero_banner">
	<div class="item position-relative" style="'.$grIMG.'">
		<div class="container text-center">
			<div class="text1 mb-lg-5 mb-3 pb-4 text-uppercase">#cont#</div>
				<div class="mb-lg-5 mb-3 pb-lg-5 pb-3 col-lg-7 mx-auto px-lg-4">
				<form action="'.$shopurl.'index.php" method="get">
					<div class="input-group input-group-lg mb-0">
						<input type="hidden" name="route" value="product/search">
						<input type="hidden" name="description" value="true">
						<button class="btn bg-transparent btn-outline-secondary rounded-0 border-end-0" type="submit"><img class="icon_search" src="'.$imageFolder.'search.svg" alt="Canabis Suche"></button>
						<input type="text" name="search" class="btn_cta form-control bg-transparent border-secondary rounded-0 border-start-0 text-center" placeholder="WAS SUCHEN SIE" autocomplete="off">
					</div>
				</form>
			</div>
			<div class="mb-0">
				<div class="mx-auto hstack gap-lg-3 gap-1 justify-content-center">
					'.($is_logged ? '' : '<a href="'.$shopurl.'index.php?route=account/account" class="btn_cta btn btn-lg py-4 btn-light text-uppercase rounded-0">Konto erstellen</a>').'
					<a href="'.$shopurl.'vorbestellen" class="btn_cta btn btn-lg py-4 btn-light text-uppercase rounded-0">Rezept hochladen</a>
					<a href="'.$shopurl.'shop" class="btn_cta btn btn-lg py-4 btn-light text-uppercase rounded-0">Unser Angebot</a>					
				</div>
				<br>
			</div>
		</div>
	</div>
</section>
';
}
else if($tref == 2) {
	$template = '
<section class="section slider">
	<div class="container-full">
		<div class="swiper myPartner">
			<div class="swiper-wrapper">		    			
#cont#
			</div>		
			<div class="swiper-button-next"></div>
			<div class="swiper-button-prev"></div>	
		</div>		
	</div>
</section>';
}

//$template = print_r($_COOKIE) . '/nam';


$ankerLink = '';
$farbe = '';
$grIMG = '';
$class = '';
$tabstand = '';
$tabstand_bottom = '';
$templateIsClosed = 1;
