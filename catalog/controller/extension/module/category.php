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

        $data[ 'categories' ] = array();

        $categories = $this->model_catalog_category->getAttributeGroups();

        //print_r($categories); die();

        foreach ( $categories as $category ) {
            $name = '<div class="item item1">';

					$name .= '<h4 class="title text1 text-black font_Inter mb-lg-4 mb-2">' . $category[ 'name' ] . '</h4>';

					$name .= $this->model_catalog_category->getCategoryAttribute($category[ 'attribute_group_id' ]);
					
					$name .= '</div>';

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

        return $this->load->view( 'extension/module/category', $data );
    }
}