<?php

class ModelCatalogAttributeGroup extends Model {
    public function addAttributeGroup( $data ) {
        $data[ 'active' ] = ($data[ 'active' ] == 'on') ? 1 : 0;

        $this->db->query( 'INSERT INTO ' . DB_PREFIX . "attribute_group SET active = '" . ( int )$data[ 'active' ] . "', sort_order = '" . ( int )$data[ 'sort_order' ] . "'" );

        $attribute_group_id = $this->db->getLastId();

        foreach ( $data[ 'attribute_group_description' ] as $language_id => $value ) {
            $this->db->query( 'INSERT INTO ' . DB_PREFIX . "attribute_group_description SET attribute_group_id = '" . ( int )$attribute_group_id . "', language_id = '" . ( int )$language_id . "', name = '" . $this->db->escape( $value[ 'name' ] ) . "'" );
        }

        return $attribute_group_id;
    }

    public function editAttributeGroup( $attribute_group_id, $data ) {
        $data[ 'active' ] = ($data[ 'active' ] == 'on') ? 1 : 0;

        $this->db->query( 'UPDATE ' . DB_PREFIX . "attribute_group SET active = '" . ( int )$data[ 'active' ] . "',  sort_order = '" . ( int )$data[ 'sort_order' ] . "' WHERE attribute_group_id = '" . ( int )$attribute_group_id . "'" );

        $this->db->query( 'DELETE FROM ' . DB_PREFIX . "attribute_group_description WHERE attribute_group_id = '" . ( int )$attribute_group_id . "'" );

        foreach ( $data[ 'attribute_group_description' ] as $language_id => $value ) {
            $this->db->query( 'INSERT INTO ' . DB_PREFIX . "attribute_group_description SET attribute_group_id = '" . ( int )$attribute_group_id . "', language_id = '" . ( int )$language_id . "', name = '" . $this->db->escape( $value[ 'name' ] ) . "'" );
        }
    }

    public function deleteAttributeGroup( $attribute_group_id ) {
        $this->db->query( 'DELETE FROM ' . DB_PREFIX . "attribute_group WHERE attribute_group_id = '" . ( int )$attribute_group_id . "'" );
        $this->db->query( 'DELETE FROM ' . DB_PREFIX . "attribute_group_description WHERE attribute_group_id = '" . ( int )$attribute_group_id . "'" );
    }

    public function getAttributeGroup( $attribute_group_id ) {
        $query = $this->db->query( 'SELECT * FROM ' . DB_PREFIX . "attribute_group WHERE attribute_group_id = '" . ( int )$attribute_group_id . "'" );

        return $query->row;
    }

    public function getAttributeGroups( $data = array() ) {
        $sql = 'SELECT * FROM ' . DB_PREFIX . 'attribute_group ag LEFT JOIN ' . DB_PREFIX . "attribute_group_description agd ON (ag.attribute_group_id = agd.attribute_group_id) WHERE agd.language_id = '" . ( int )$this->config->get( 'config_language_id' ) . "'";

        $sort_data = array(
            'agd.name',
            'ag.sort_order'
        );

        if ( isset( $data[ 'sort' ] ) && in_array( $data[ 'sort' ], $sort_data ) ) {
            $sql .= ' ORDER BY ' . $data[ 'sort' ];
        } else {
            $sql .= ' ORDER BY agd.name';
        }

        if ( isset( $data[ 'order' ] ) && ( $data[ 'order' ] == 'DESC' ) ) {
            $sql .= ' DESC';
        } else {
            $sql .= ' ASC';
        }

        if ( isset( $data[ 'start' ] ) || isset( $data[ 'limit' ] ) ) {
            if ( $data[ 'start' ] < 0 ) {
                $data[ 'start' ] = 0;
            }

            if ( $data[ 'limit' ] < 1 ) {
                $data[ 'limit' ] = 20;
            }

            $sql .= ' LIMIT ' . ( int )$data[ 'start' ] . ',' . ( int )$data[ 'limit' ];
        }

        $query = $this->db->query( $sql );

        return $query->rows;
    }

    public function getAttributeGroupsOfCategory( $category_id ) {
        $response = array();
        $attributes = array();

        $sql = 'SELECT * FROM ' . DB_PREFIX . "category c WHERE c.category_id = '" . ( int )$category_id . "'";

        $query = $this->db->query( $sql );

        try {
            if ( $query->row[ 'attributes' ] != '' )
            $attributes = json_decode( $query->row[ 'attributes' ] );
        } catch( Exception $e ) {
            $attributes = array();
        }

        $sql = 'SELECT * FROM ' . DB_PREFIX . 'attribute_group ag LEFT JOIN ' . DB_PREFIX . "attribute_group_description agd ON (ag.attribute_group_id = agd.attribute_group_id) WHERE ag.attribute_group_id <> 12 and ag.attribute_group_id <> 14 and agd.language_id = '" . ( int )$this->config->get( 'config_language_id' ) . "'";

        $sort_data = array(
            'agd.name',
            'ag.sort_order'
        );

        if ( isset( $data[ 'sort' ] ) && in_array( $data[ 'sort' ], $sort_data ) ) {
            $sql .= ' ORDER BY ' . $data[ 'sort' ];
        } else {
            $sql .= ' ORDER BY agd.name';
        }

        if ( isset( $data[ 'order' ] ) && ( $data[ 'order' ] == 'DESC' ) ) {
            $sql .= ' DESC';
        } else {
            $sql .= ' ASC';
        }

        if ( isset( $data[ 'start' ] ) || isset( $data[ 'limit' ] ) ) {
            if ( $data[ 'start' ] < 0 ) {
                $data[ 'start' ] = 0;
            }

            if ( $data[ 'limit' ] < 1 ) {
                $data[ 'limit' ] = 20;
            }

            $sql .= ' LIMIT ' . ( int )$data[ 'start' ] . ',' . ( int )$data[ 'limit' ];
        }

        $query = $this->db->query( $sql );

        //print_r( $query->rows );
        //die();

        foreach ( $query->rows as $result ) {
            if ( in_array( $result[ 'attribute_group_id' ], $attributes ) )
            $result[ 'is_select' ] = true;
            else
            $result[ 'is_select' ] = false;

            $response[] = $result;
        }

        return $response;

    }

    public function getAttributeGroupDescriptions( $attribute_group_id ) {
        $attribute_group_data = array();

        $query = $this->db->query( 'SELECT * FROM ' . DB_PREFIX . "attribute_group_description WHERE attribute_group_id = '" . ( int )$attribute_group_id . "'" );

        foreach ( $query->rows as $result ) {
            $attribute_group_data[ $result[ 'language_id' ] ] = array( 'name' => $result[ 'name' ] );
        }

        return $attribute_group_data;
    }

    public function getTotalAttributeGroups() {
        $query = $this->db->query( 'SELECT COUNT(*) AS total FROM ' . DB_PREFIX . 'attribute_group' );

        return $query->row[ 'total' ];
    }
}