<?php
class ModelLocalisationWeightClass extends Model {
	public function getWeightClass($weight_class_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "weight_class_description WHERE weight_class_id = '" . (int)$weight_class_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}
}