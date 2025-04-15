<?php

class ControllerExtensionModuleCategory extends Controller {
    public function index() {
        $this->load->language( 'extension/module/category' );

        if ( isset( $this->request->get[ 'path' ] ) ) {
            $parts = explode( '_', ( string )$this->request->get[ 'path' ] );
        } else {
            $parts = array();
        }

        if ( isset( $parts[ 0 ] ) ) {
            $data[ 'category_id' ] = $parts[ 0 ];
        } else {
            $data[ 'category_id' ] = 0;
        }

        if ( isset( $parts[ 1 ] ) ) {
            $data[ 'child_id' ] = $parts[ 1 ];
        } else {
            $data[ 'child_id' ] = 0;
        }

        $this->load->model( 'catalog/category' );

        $this->load->model( 'catalog/product' );

        $this->load->model('catalog/manufacturer');

        $data[ 'categories' ] = array();

        $categories = $this->model_catalog_category->getAttributeGroups();

        $manufacturers = $this->model_catalog_manufacturer->getManufacturerHasProduct();
        //print_r($manufacturers); die();

        $childManufactor = '';

        $logged = $this->customer->isLogged();
		$data['logged'] = $logged;
        // print_r($data); 
		// die();
		//print_r($categories); die();

// 
// 		<div class="col">
// 			<div class="mega-link">
// 				Mega Menu 1
// 				<div class="mega-menu ">
// 					<div class="row">
// 						<div class="col">
// 							<div class="row">
// 								<a href="#" class="nav-link col-xl-2 col-lg-3 col-6">Unterpunkt 1</a>
// 								<a href="#" class="nav-link col-xl-2 col-lg-3 col-6">Unterpunkt 2</a>
// 								<a href="#" class="nav-link col-xl-2 col-lg-3 col-6">Unterpunkt 3</a>
// 							</div>
// 						</div>
// 					</div>
// 				</div>
// 			</div>
// 		</div>


        foreach ( $categories as $category ) {
            $name = '<div class="col item item-cat ' . $category[ 'name' ] . ' dd'.$category[ 'attribute_group_id' ].'"><div class="mega-link">';

				$name .= '<h4 class="title text1 text-black categoryHL font_Inter ' . $category[ 'name' ] . '">' . $category[ 'name' ] . '</h4>
				<div class="mega-menu ">';

				$name .= $this->model_catalog_category->getCategoryAttribute($category[ 'attribute_group_id' ]);
					
			$name .= '</div></div></div>';

					//$name = 'ndd';

                    //echo $this->model_catalog_category->getCategoryAttribute( $child[ 'category_id' ] );

                    /*$children_data[] = array(
                        'category_id' => $child[ 'category_id' ],
                        'name' => $name,
                        'href' => $this->url->link( 'product/category', 'path=' . $category[ 'category_id' ] . '_' . $child[ 'category_id' ] )
                    );*/
                
            

            $filter_data = array(
                'filter_category_id'  => $category[ 'category_id' ],
                'filter_sub_category' => true
            );

            $data[ 'categories' ][] = array(
                'category_id' => $category[ 'category_id' ],
                'name'        => $name, //$category[ 'name' ],
                'children'    => $children_data,
                'href'        => $this->url->link( 'product/category', 'path=' . $category[ 'category_id' ] )
            );
        }

        // show filter by manufactor
        $title = 'Hersteller';
        $name = '<div class="col item item-cat manufactor"><div class="mega-link">';

        $name .= '<h4 class="title text1 text-black categoryHL font_Inter ' . $title . '">' . $title . '</h4>
        <div class="mega-menu ">';

        $name .= $this->showManufactor($manufacturers);
					
        $name .= '</div></div></div>';
        
        $data[ 'categories' ][] = array(
            'category_id' => null, //$category[ 'category_id' ],
            'name'        => $name, //$category[ 'name' ],
            'children'    => null, //$children_data,
            'href'        => null //$this->url->link( 'product/category', 'path=' . $category[ 'category_id' ] )
        );

        return $this->load->view( 'extension/module/category', $data );
    }

    public function showManufactor($manufactors) {
        foreach ( $manufactors as $row ) {
            $response .= '<span href="javascript:void(0)" class="nav-link col-xl-2 col-lg-3 col-6 col-md-4 text-secondary text2 no-wrap"><input id="manufactor_'.$row['manufacturer_id'].'" type="checkbox" class="filter_attribute filter_manufactor" data="'.$row['manufacturer_id'].'"/><label class="label_atb" for="manufactor_'.$row['manufacturer_id'].'">'.$row[ 'name' ].'</label></span>';
        }

        // $response .= '</div>';
		$response .= '';
        
		return $response;
    }
}
