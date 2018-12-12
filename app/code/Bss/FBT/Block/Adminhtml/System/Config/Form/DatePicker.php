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
namespace Bss\FBT\Block\Adminhtml\System\Config\Form;

use Magento\Framework\Registry;
use Magento\Backend\Block\Template\Context;

class DatePicker extends \Magento\Config\Block\System\Config\Form\Field
{
	protected $_coreRegistry;

	public function __construct(
		Context $context,
		Registry $coreRegistry,
		array $data = []
	) {
		$this->_coreRegistry = $coreRegistry;
		parent::__construct($context, $data);
	}

	protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
	{
		$html = $element->getElementHtml();
			if (!$this->_coreRegistry->registry('datepicker_loaded')) {
				$this->_coreRegistry->registry('datepicker_loaded', 1);
			}
				$html .= '<button type="button" style="display:none;" class="ui-datepicker-trigger '
				.'v-middle"><span>Select Date</span></button>';
				$html .= '<style type="text/css">#row_fbt_general_start_date button.ui-datepicker-trigger.v-middle:after{-webkit-font-smoothing:antialiased;font-size:2.1rem;line-height:32px;color:#514943;content:"\e627";font-family:"Admin Icons";vertical-align:middle;display:inline-block;font-weight:400;overflow:hidden;speak:none;text-align:center}#row_fbt_general_start_date button.ui-datepicker-trigger.v-middle{background:0 0;-moz-box-sizing:content-box;border:0;box-shadow:none;line-height:inherit;margin:0 0 0 -3.2rem;padding:0;text-shadow:none;font-weight:400;text-decoration:none;display:inline-block;height:3.2rem;overflow:hidden;position:absolute;vertical-align:top;z-index:1}#row_fbt_general_start_date button.ui-datepicker-trigger.v-middle span{border:0;clip:rect(0,0,0,0);height:1px;margin:-1px;overflow:hidden;padding:0;position:absolute;width:1px}</style>';
				$html .= '<script type="text/javascript">
				require(["jquery", "jquery/ui"], function (jq) {
				jq(document).ready(function () {
					jq("#' . $element->getHtmlId() . '").datepicker( { dateFormat: "dd/mm/yy",maxDate: new Date() } );
						jq(".ui-datepicker-trigger").removeAttr("style");
							jq(".ui-datepicker-trigger").click(function(){
								jq("#' . $element->getHtmlId() . '").focus();
							});
						});
					});
				</script>';
		return $html;
	}
}