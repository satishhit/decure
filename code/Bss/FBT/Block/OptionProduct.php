<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    Bss_FBT
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\FBT\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Pricing\Price\CustomOptionPriceInterface;

class OptionProduct extends \Magento\Framework\View\Element\Template
{
    protected $objectManager;
    protected $objectFactory;
    protected $customerSession;
    protected $_fillLeadingZeros = true;
    protected $_catalogProductOptionTypeDate;
    protected $pricingHelper;
    protected $_blockFactory;
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        \Magento\Catalog\Model\Product\Option\Type\Date $catalogProductOptionTypeDate,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->_blockFactory   = $blockFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->pricingHelper   = $pricingHelper;
        $this->_catalogProductOptionTypeDate = $catalogProductOptionTypeDate;
        parent::__construct($context, $data);
    }
    public function getProductOptionsHtml($product,$configurable = false)
        { 
            $blockOption = $this->getLayout()->createBlock("Magento\Catalog\Block\Product\View\Options");
            $blockOptionsHtml = null;

             if($product->getTypeId()=="simple"||$product->getTypeId()=="virtual" || $product->getTypeId()=="configurable")
             {  
                $blockOption->setProduct($product);
                $customOptions = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Catalog\Model\Product\Option')->getProductOptionCollection($product);
                if($customOptions)
                {  
                    foreach ($customOptions as $_option) 
                    {     
                        $blockOptionsHtml .= $this->getValuesHtml($_option,$product); 
                    };    
                }  
             } 

             if($product->getTypeId()=="bundle")
             {  

                $store_id = $this->_storeManager->getStore()->getId();

                $options = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Bundle\Model\Option')
                                           ->getResourceCollection()
                                           ->setProductIdFilter($product->getId())
                                           ->setPositionOrder();
                 
                $options->joinValues($store_id);
                 
                $typeInstance = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Bundle\Model\Product\Type');
                 
                $selections = $typeInstance->getSelectionsCollection($typeInstance->getOptionsIds($product), $product);
                foreach( $options as $option ){
                    $classtt = '';
                    $em = '';
                    if ($option->getRequired()) {
                        $classtt = 'required';
                        $em =  '<em>*</em>';
                    }
                    $blockOptionsHtml.= "<dt><label class='".$classtt."'>".$option->getDefaultTitle().$em."</label></dt>";
                    $blockOptionsHtml.= "<dd><select id='bundle-option-".$option->getId()."' name='bundle_option[".$option->getId()."]'>";
                    $blockOptionsHtml.="<option value=''>Choose a selection...</option>";
                    foreach( $selections as $selection ){
                        if ($selection->getOptionId() == $option->getId()) {
                            $blockOptionsHtml.="<option value='".$selection->getSelectionId()."'>".$selection->getName()."</option>";
                        } 
                    }
                    $blockOptionsHtml.="</select></dd>";
                    $blockOptionsHtml.="<input type='text' name='bundle_option_qty[".$option->getId()."]' value='1' style='width: 3.2em;' />";
                }
                      
             }
             // if($product->getTypeId()=="downloadable")
             // {   
                
             // }
             if($product->getTypeId()=="configurable" && $configurable)
             {   
                $productTypeInstance = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable')->getConfigurableAttributesAsArray($product);

                foreach ($productTypeInstance as $_attributeId => $_attribute){
                    $blockOptionsHtml .= '<div class="field configurable required">';
                    $blockOptionsHtml .= '<label class="label" for="attribute'.$_attributeId.'">';
                    $blockOptionsHtml .= '<span>'.htmlspecialchars($_attribute['label']).'</span>';
                    $blockOptionsHtml .= '</label>';
                    $blockOptionsHtml .= '<div class="control">';
                    $blockOptionsHtml .= '<select name="super_attribute['.$_attributeId.']"';
                    $blockOptionsHtml .= 'data-selector="super_attribute['.$_attributeId.']"';
                    $blockOptionsHtml .= 'data-validate="{required:true}"';
                    $blockOptionsHtml .= 'id="attribute'.$_attributeId.'"';
                    $blockOptionsHtml .= 'class="super-attribute-select">';
                    $blockOptionsHtml .= '<option value="">Choose an Option...</option>';
                    foreach ($_attribute['values'] as $attribute) {
                            $blockOptionsHtml .= '<option value="'.$attribute['value_index'].'">'.$attribute['store_label'].'</option>';
                    }
                    $blockOptionsHtml .= '</select></div></div>';
                }
             }  
             return '<div class="fieldset" tabindex="0">'.$blockOptionsHtml.'</div>'; 
        }
    public function getValuesHtml($_option,$product)
    {
        $configValue = $product->getPreconfiguredValues()->getData('options/' . $_option->getId());
        $store = $product->getStore();

        $class = ($_option->getIsRequire()) ? ' required' : '';
        $html = '';
        // Remove inline prototype onclick and onchange events
        if ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_FIELD ||
            $_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_AREA
        ) {

        $html .= '<div class="field';
        if ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_AREA) {
            $html .= ' textarea ';
        }
        $html .= $class.'">';
        $html .='<label class="label" for="options_'.$_option->getId().'_text">
        <span>'.htmlspecialchars($_option->getTitle()).'</span>
        </label>';

        $html .='<div class="control">';
        if ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_FIELD){
            $_textValidate = null;
            if ($_option->getIsRequire()) {
                $_textValidate['required'] = true;
            }
            if ($_option->getMaxCharacters()) {
                $_textValidate['maxlength'] = $_option->getMaxCharacters();
            }
        $html .='<input type="text"
                   id="options_'.$_option->getId().'_text"
                   class="input-text product-custom-option"';
            if (!empty($_textValidate)) {
                $html .='data-validate="'.htmlspecialchars(json_encode($_textValidate)).'"';
            }
        $html .='name="options['.$_option->getId().']"
                   data-selector="options['.$_option->getId().']"
                   value="'. htmlspecialchars($product->getPreconfiguredValues()->getData('options/' . $_option->getId())).'"/>';
        }
        if ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_AREA){
            $_textAreaValidate = null;
            if ($_option->getIsRequire()) {
                $_textAreaValidate['required'] = true;
            }
            if ($_option->getMaxCharacters()) {
                $_textAreaValidate['maxlength'] = $_option->getMaxCharacters();
            }
        $html .='<textarea id="options_'.$_option->getId().'_text"
                      class="product-custom-option"';
            if (!empty($_textAreaValidate)) {
                $html .='data-validate="'.htmlspecialchars(json_encode($_textAreaValidate)).'"';
             }
        $html .='name="options['.$_option->getId().']"
                      data-selector="options['.$_option->getId().']"
                      rows="5"
                      cols="25">'.htmlspecialchars($product->getPreconfiguredValues()->getData('options/' . $_option->getId())).'</textarea>';
        }
        if ($_option->getMaxCharacters()){
            $html .='<p class="note">Maximum number of characters:
                <strong>'.$_option->getMaxCharacters().'</strong></p>';
        }
        $html .='</div></div>';

        }
        if ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DATE_TIME ||
            $_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DATE ||
            $_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_TIME
        ) {
            $html .='<div class="field date'.$class.'"';
            $html .='">
                <fieldset class="fieldset fieldset-product-options-inner'.$class.'">
                    <legend class="legend">
                        <span>'.htmlspecialchars($_option->getTitle()).'</span>                        
                    </legend>';
            $html .='<div class="control">';
            if ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DATE_TIME
                || $_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DATE){

                $html .= $this->getDateHtml($_option,$product);

            }

            if ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DATE_TIME
                || $_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_TIME){
                $html .= $this->getTimeHtml($_option,$product);
            }

            if ($_option->getIsRequire()){
                $html .='<input type="hidden"
                                   name="validate_datetime_'.$_option->getId().'"
                                   class="validate-datetime-'.$_option->getId().'"
                                   value=""
                                   data-validate="{"validate-required-datetime":'.$_option->getId().'}"/>';
            }else{
                $html .='<input type="hidden"
                                   name="validate_datetime_'.$_option->getId().'"
                                   class="validate-datetime-'.$_option->getId().'"
                                   value=""
                                   data-validate="{"validate-optional-datetime":'.$_option->getId().'}"/>';
            }
           
            $html .='</div></fieldset></div>';

        }
        // if ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_GROUP_FILE) {
            
        // }
        if ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DROP_DOWN ||
            $_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_MULTIPLE
        ) {
            $html .= '<div class="field'.$class.'">';
            $html .='<label class="label" for="select_'.$_option->getId().'">
                    <span>'.htmlspecialchars($_option->getTitle()).'</span>
                    </label>';
            $html .='<div class="control">';
            $extraParams = '';
            $select = $this->getLayout()->createBlock(
                \Magento\Framework\View\Element\Html\Select::class
            )->setData(
                [
                    'id' => 'select_' . $_option->getId(),
                    'class' => $class . ' product-custom-option admin__control-select'
                ]
            );
            if ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_DROP_DOWN) {
                $select->setName('options[' . $_option->getid() . ']')->addOption('', __('-- Please Select --'));
            } else {
                $select->setName('options[' . $_option->getid() . '][]');
                $select->setClass('multiselect admin__control-multiselect' . $class . ' product-custom-option');
            }
            foreach ($_option->getValues() as $_value) {
                $priceStr = $this->_formatPrice(
                    $_option,
                    $product,
                    [
                        'is_percent' => $_value->getPriceType() == 'percent',
                        'pricing_value' => $_value->getPrice($_value->getPriceType() == 'percent'),
                    ],
                    false
                );
                $select->addOption(
                    $_value->getOptionTypeId(),
                    $_value->getTitle() . ' ' . strip_tags($priceStr) . '',
                    ['price' => $this->pricingHelper->currencyByStore($_value->getPrice(true), $store, false)]
                );
            }
            if ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_MULTIPLE) {
                $extraParams = ' multiple="multiple"';
            }
            $extraParams .= ' data-selector="' . $select->getName() . '"';
            $select->setExtraParams($extraParams);

            if ($configValue) {
                $select->setValue($configValue);
            }
            $html .= $select->getHtml();
            $html .='</div></div>';
            // return $select->getHtml();
        }

        if ($_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_RADIO ||
            $_option->getType() == \Magento\Catalog\Model\Product\Option::OPTION_TYPE_CHECKBOX
        ) {
            $html .= '<div class="field'.$class.'">';
            $html .='<label class="label" for="select_'.$_option->getId().'">
                    <span>'.htmlspecialchars($_option->getTitle()).'</span>
                    </label>';
            $html .='<div class="control">';
            $selectHtml = '<div class="options-list nested" id="options-' . $_option->getId() . '-list">';
            $arraySign = '';
            switch ($_option->getType()) {
                case \Magento\Catalog\Model\Product\Option::OPTION_TYPE_RADIO:
                    $type = 'radio';
                    $classs = 'radio admin__control-radio';
                    if (!$_option->getIsRequire()) {
                        $selectHtml .= '<div class="field choice admin__field admin__field-option">' .
                            '<input type="radio" id="options_' .
                            $_option->getId() .
                            '" class="' .
                            $classs .
                            ' product-custom-option" name="options[' .
                            $_option->getId() .
                            ']"' .
                            ' data-selector="options[' . $_option->getId() . ']"' .
                            ' value="" checked="checked" /><label class="label admin__field-label" for="options_' .
                            $_option->getId() .
                            '"><span>' .
                            __('None') . '</span></label></div>';
                    }
                    break;
                case \Magento\Catalog\Model\Product\Option::OPTION_TYPE_CHECKBOX:
                    $type = 'checkbox';
                    $classs = 'checkbox admin__control-checkbox';
                    $arraySign = '[]';
                    break;
            }
            $count = 1;
            foreach ($_option->getValues() as $_value) {
                $count++;

                $priceStr = $this->_formatPrice(
                    $_option,
                    $product,
                    [
                        'is_percent' => $_value->getPriceType() == 'percent',
                        'pricing_value' => $_value->getPrice($_value->getPriceType() == 'percent'),
                    ]
                );

                $htmlValue = $_value->getOptionTypeId();
                if ($arraySign) {
                    $checked = is_array($configValue) && in_array($htmlValue, $configValue) ? 'checked' : '';
                } else {
                    $checked = $configValue == $htmlValue ? 'checked' : '';
                }

                $dataSelector = 'options[' . $_option->getId() . ']';
                if ($arraySign) {
                    $dataSelector .= '[' . $htmlValue . ']';
                }

                $selectHtml .= '<div class="field choice admin__field admin__field-option' .
                    $class .
                    '">' .
                    '<input type="' .
                    $type .
                    '" class="' .
                    $classs .
                    ' ' .
                    $class .
                    ' product-custom-option"' .
                    ' name="options[' .
                    $_option->getId() .
                    ']' .
                    $arraySign .
                    '" id="options_' .
                    $_option->getId() .
                    '_' .
                    $count .
                    '" value="' .
                    $htmlValue .
                    '" ' .
                    $checked .
                    ' data-selector="' . $dataSelector . '"' .
                    ' price="' .
                    $this->pricingHelper->currencyByStore($_value->getPrice(true), $store, false) .
                    '" />' .
                    '<label class="label admin__field-label" for="options_' .
                    $_option->getId() .
                    '_' .
                    $count .
                    '"><span>' .
                    $_value->getTitle() .
                    '</span> ' .
                    $priceStr .
                    '</label>';
                $selectHtml .= '</div>';
            }
            $selectHtml .= '</div>';
            $html .= $selectHtml;
            if ($_option->getIsRequire()){
                $html .='<span id="options-'.$_option->getId() .'-container"></span>';
            }
            $html .='</div></div>';
        }
        return $html;
    }

    protected function _formatPrice($option,$product,$value, $flag = true)
    {
        if ($value['pricing_value'] == 0) {
            return '';
        }

        $sign = '+';
        if ($value['pricing_value'] < 0) {
            $sign = '-';
            $value['pricing_value'] = 0 - $value['pricing_value'];
        }

        $priceStr = $sign;
        $resultPage = $this->resultPageFactory->create();
        $customOptionPrice = $product->getPriceInfo()->getPrice('custom_option_price');
        $context = [CustomOptionPriceInterface::CONFIGURATION_OPTION_FLAG => true];
        $optionAmount = $customOptionPrice->getCustomAmount($value['pricing_value'], null, $context);
        $priceStr .= $resultPage->getLayout()->getBlock('product.price.render.default')->renderAmount(
            $optionAmount,
            $customOptionPrice,
            $product
        );
        if ($flag) {
            $priceStr = '<span class="price-notice">' . $priceStr . '</span>';
        }

        return $priceStr;
    }

    public function getDateHtml($_option,$product)
    {
        if ($this->_catalogProductOptionTypeDate->useCalendar()) {
            return $this->getCalendarDateHtml($_option,$product);
        } else {
            return $this->getDropDownsDateHtml($_option,$product);
        }
    }

    public function getCalendarDateHtml($_option,$product)
    {
        $value = $product->getPreconfiguredValues()->getData('options/' . $_option->getId() . '/date');

        $yearStart = $this->_catalogProductOptionTypeDate->getYearStart();
        $yearEnd = $this->_catalogProductOptionTypeDate->getYearEnd();

        $calendar = $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Html\Date::class
        )->setId(
            'options_' . $_option->getId() . '_date'
        )->setName(
            'options[' . $_option->getId() . '][date]'
        )->setClass(
            'product-custom-option datetime-picker input-text'
        )->setImage(
            $this->getViewFileUrl('Magento_Theme::calendar.png')
        )->setDateFormat(
            $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT)
        )->setValue(
            $value
        )->setYearsRange(
            $yearStart . ':' . $yearEnd
        );

        return $calendar->getHtml();
    }

    public function getDropDownsDateHtml($_option,$product)
    {
        $fieldsSeparator = '&nbsp;';
        $fieldsOrder = $this->_catalogProductOptionTypeDate->getConfigData('date_fields_order');
        $fieldsOrder = str_replace(',', $fieldsSeparator, $fieldsOrder);

        $monthsHtml = $this->_getSelectFromToHtml($_option,$product,'month', 1, 12);
        $daysHtml = $this->_getSelectFromToHtml($_option,$product,'day', 1, 31);

        $yearStart = $this->_catalogProductOptionTypeDate->getYearStart();
        $yearEnd = $this->_catalogProductOptionTypeDate->getYearEnd();
        $yearsHtml = $this->_getSelectFromToHtml($_option,$product,'year', $yearStart, $yearEnd);

        $translations = ['d' => $daysHtml, 'm' => $monthsHtml, 'y' => $yearsHtml];
        return strtr($fieldsOrder, $translations);
    }

    public function getTimeHtml($_option,$product)
    {
        if ($this->_catalogProductOptionTypeDate->is24hTimeFormat()) {
            $hourStart = 0;
            $hourEnd = 23;
            $dayPartHtml = '';
        } else {
            $hourStart = 1;
            $hourEnd = 12;
            $dayPartHtml = $this->_getHtmlSelect(
                $_option,
                $product,
                'day_part'
            )->setOptions(
                ['am' => __('AM'), 'pm' => __('PM')]
            )->getHtml();
        }
        $hoursHtml = $this->_getSelectFromToHtml($_option,$product,'hour', $hourStart, $hourEnd);
        $minutesHtml = $this->_getSelectFromToHtml($_option,$product,'minute', 0, 59);

        return $hoursHtml . '&nbsp;<b>:</b>&nbsp;' . $minutesHtml . '&nbsp;' . $dayPartHtml;
    }


    protected function _getSelectFromToHtml($_option,$product,$name, $from, $to, $value = null)
    {
        $options = [['value' => '', 'label' => '-']];
        for ($i = $from; $i <= $to; $i++) {
            $options[] = ['value' => $i, 'label' => $this->_getValueWithLeadingZeros($i)];
        }
        return $this->_getHtmlSelect($_option,$product,$name, $value)->setOptions($options)->getHtml();
    }


    protected function _getHtmlSelect($_option,$product,$name, $value = null)
    {
        // $require = $this->getOption()->getIsRequire() ? ' required-entry' : '';
        $require = '';
        $select = $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Html\Select::class
        )->setId(
            'options_' . $_option->getId() . '_' . $name
        )->setClass(
            'product-custom-option admin__control-select datetime-picker' . $require
        )->setExtraParams()->setName(
            'options[' . $_option->getId() . '][' . $name . ']'
        );

        $extraParams = 'style="width:auto"';

        $extraParams .= ' data-role="calendar-dropdown" data-calendar-role="' . $name . '"';
        $extraParams .= ' data-selector="' . $select->getName() . '"';
        if ($_option->getIsRequire()) {
            $extraParams .= ' data-validate=\'{"datetime-validation": true}\'';
        }

        $select->setExtraParams($extraParams);
        if ($value === null) {
            $value = $product->getPreconfiguredValues()->getData(
                'options/' . $_option->getId() . '/' . $name
            );
        }
        if ($value !== null) {
            $select->setValue($value);
        }

        return $select;
    }

    protected function _getValueWithLeadingZeros($value)
    {
        if (!$this->_fillLeadingZeros) {
            return $value;
        }
        return $value < 10 ? '0' . $value : $value;
    }
   

}