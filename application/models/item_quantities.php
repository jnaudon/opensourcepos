<?php
class Item_quantities extends CI_Model
{
    function exists($item_id,$location_id,$unit_id)
    {
        $this->db->from('item_quantities');
        $this->db->where('item_id',$item_id);
        $this->db->where('location_id',$location_id);
        $this->db->where('unit_id',$unit_id);
        $query = $this->db->get();

        return ($query->num_rows()==1);
    }
    
    function save($location_detail, $item_id, $location_id, $unit_id)
    {
        if (!$this->exists($item_id,$location_id, $unit_id))
        {
            if($this->db->insert('item_quantities',$location_detail))
            {
                return true;
            }
            return false;
        }

        $this->db->where('item_id', $item_id);
        $this->db->where('location_id', $location_id);
        $this->db->where('unit_id', $unit_id);
        return $this->db->update('item_quantities',$location_detail);
    }
    
    function get_item_quantities($item_id, $location_id)
    {
    	$this->db->from('item_quantities');
    	$this->db->join('item_units', 'item_units.unit_id=item_quantities.unit_id');
    	$this->db->where('item_id',$item_id);
    	$this->db->where('location_id',$location_id);
    	return $this->db->get()->result_array();
    }
    
    function get_item_unit_quantity($item_id, $location_id, $unit_id)
    {
    	$this->db->select('GROUP_CONCAT(quantity, unit_name SEPARATOR \' \') AS quantity', FALSE);
    	$this->db->from('item_quantities');
    	$this->db->join('item_units','item_units.unit_id=item_quantities.unit_id');
    	$this->db->where('location_id', $location_id);
    	$this->db->where('item_quantities.unit_id', $unit_id);
    	$this->db->where('item_id', $item_id);
    	return $this->db->get()->row();
    }
    
    function get_item_quantity($item_id, $location_id, $unit_id)
    {     
        $this->db->from('item_quantities');
        $this->db->where('item_id',$item_id);
        $this->db->where('location_id',$location_id);
	    $this->db->where('unit_id',$unit_id);
        $result = $this->db->get()->row();
        if(empty($result) == true)
        {
            //Get empty base parent object, as $item_id is NOT an item
            $result=new stdClass();
            //Get all the fields from items table (TODO to be reviewed)
            $fields = $this->db->list_fields('item_quantities');
            foreach ($fields as $field)
            {
                $result->$field='';
            }
        }          
        return $result;   
    }
	
	/*
	 * changes to quantity of an item according to the given amount.
	 * if $quantity_change is negative, it will be subtracted,
	 * if it is positive, it will be added to the current quantity
	 */
	function change_quantity($item_id, $location_id, $unit_id, $quantity_change)
	{
		$quantity_old = $this->get_item_quantity($item_id, $location_id, $unit_id);
		$quantity_new = $quantity_old->quantity + intval($quantity_change);
		$location_detail = array('item_id'=>$item_id,
									'location_id'=>$location_id,
									'unit_id'=>$unit_id,				
									'quantity'=>$quantity_new);
		return $this->save($location_detail,$item_id,$location_id,$unit_id);
	}
}
?>