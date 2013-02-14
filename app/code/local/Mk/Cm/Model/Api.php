<?php
class Mk_Cm_Model_Api extends Mage_Api_Model_Resource_Abstract
{        

    public function items($filters = null)
    {
        $collection = Mage::getResourceModel('sales/order_creditmemo_collection')
            ->addAttributeToSelect('increment_id')
            ->addAttributeToSelect('state');

        $result = array();

        foreach ($collection as $creditmemo) {
            $result[] = $this->_getAttributes($creditmemo, 'creditmemo');
        }

        return $result;
    }

    protected $_ignoredAttributeCodes = array(
        'global'    =>  array('entity_id', 'attribute_set_id', 'entity_type_id')
    );

    /**
     * Attributes map array per entity type
     *
     * @var google
     */
    protected $_attributesMap = array(
        'global'    => array()
    );

    /**
     * Update attributes for entity
     *
     * @param array $data
     * @param Mage_Core_Model_Abstract $object
     * @param array $attributes
     * @return Mage_Sales_Model_Api_Resource
     */
    protected function _updateAttributes($data, $object, $type,  array $attributes = null)
    {

        foreach ($data as $attribute=>$value) {
            if ($this->_isAllowedAttribute($attribute, $type, $attributes)) {
                $object->setData($attribute, $value);
            }
        }

        return $this;
    }

    /**
     * Retrieve entity attributes values
     *
     * @param Mage_Core_Model_Abstract $object
     * @param array $attributes
     * @return Mage_Sales_Model_Api_Resource
     */
    protected function _getAttributes($object, $type, array $attributes = null)
    {
        $result = array();

        if (!is_object($object)) {
            return $result;
        }

        foreach ($object->getData() as $attribute=>$value) {
            if ($this->_isAllowedAttribute($attribute, $type, $attributes)) {
                $result[$attribute] = $value;
            }
        }

        foreach ($this->_attributesMap['global'] as $alias=>$attributeCode) {
            $result[$alias] = $object->getData($attributeCode);
        }

        if (isset($this->_attributesMap[$type])) {
            foreach ($this->_attributesMap[$type] as $alias=>$attributeCode) {
                $result[$alias] = $object->getData($attributeCode);
            }
        }

        return $result;
    }

    /**
     * Check is attribute allowed to usage
     *
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param string $entityType
     * @param array $attributes
     * @return boolean
     */
    protected function _isAllowedAttribute($attributeCode, $type, array $attributes = null)
    {
        if (!empty($attributes)
            && !(in_array($attributeCode, $attributes))) {
            return false;
        }

        if (in_array($attributeCode, $this->_ignoredAttributeCodes['global'])) {
            return false;
        }

        if (isset($this->_ignoredAttributeCodes[$type])
            && in_array($attributeCode, $this->_ignoredAttributeCodes[$type])) {
            return false;
        }

        return true;
    }


    /**
     * Load CreditMemo by IncrementId
     *
     * @param mixed $incrementId
     * @return Mage_Core_Model_Abstract|Mage_Sales_Model_Order_Creditmemo
     */
    protected function _getCreditmemo($incrementId)
    {
        /** @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
        $creditmemo = Mage::getModel('sales/order_creditmemo')->load($incrementId, 'increment_id');
        if (!$creditmemo->getId()) {
            $this->_fault('not_exists');
        }
        return $creditmemo;
    }



    /**
     * Retrieve credit memo information
     *
     * @param string $creditmemoIncrementId
     * @return array
     */
    public function info($creditmemoIncrementId)
    {
        $creditmemo = $this->_getCreditmemo($creditmemoIncrementId);
        // get credit memo attributes with entity_id' => 'creditmemo_id' mapping
        $result = $this->_getAttributes($creditmemo, 'creditmemo');
        $result['order_increment_id'] = $creditmemo->getOrder()->load($creditmemo->getOrderId())->getIncrementId();
        // items refunded
        $result['items'] = array();
        foreach ($creditmemo->getAllItems() as $item) {
            $result['items'][] = $this->_getAttributes($item, 'creditmemo_item');
        }
        // credit memo comments
        $result['comments'] = array();
        foreach ($creditmemo->getCommentsCollection() as $comment) {
            $result['comments'][] = $this->_getAttributes($comment, 'creditmemo_comment');
        }

        return $result;
    }



}