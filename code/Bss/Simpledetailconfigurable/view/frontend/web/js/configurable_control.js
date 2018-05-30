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
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

define([
    'jquery',
    'underscore',
    'Magento_Catalog/js/price-utils',
    'mage/translate',
    'jquery/ui',
    'jquery/jquery.parsequery'
], function ($, _, priceUtils, $t) {
    'use strict';

    /**
     * Render tooltips by attributes (only to up).
     * Required element attributes:
     *  - option-type (integer, 0-3)
     *  - option-label (string)
     *  - option-tooltip-thumb
     *  - option-tooltip-value
     */
    $.widget('mage.SwatchRendererTooltip', {
        options: {
            delay: 200,                             //how much ms before tooltip to show
            tooltipClass: 'swatch-option-tooltip'  //configurable, but remember about css
        },

        /**
         * @private
         */
        _init: function () {
            var $widget = this,
                $this = this.element,
                $element = $('.' + $widget.options.tooltipClass),
                timer,
                type = parseInt($this.attr('option-type'), 10),
                label = $this.attr('option-label'),
                thumb = $this.attr('option-tooltip-thumb'),
                value = $this.attr('option-tooltip-value'),
                $image,
                $title,
                $corner;

            if (!$element.size()) {
                $element = $('<div class="'
                    + $widget.options.tooltipClass
                    + '"><div class="image"></div><div class="title"></div><div class="corner"></div></div>');
                $('body').append($element);
            }
            $image = $element.find('.image');
            $title = $element.find('.title');
            $corner = $element.find('.corner');

            $this.hover(function () {
                if (!$this.hasClass('disabled')) {
                    timer = setTimeout(
                        function () {
                            var leftOpt = null,
                                leftCorner = 0,
                                left,
                                $window;

                            if (type === 2) {
                                // Image
                                $image.css({
                                    'background': 'url("' + thumb + '") no-repeat center', //Background case
                                    'background-size': 'initial'
                                });
                                $image.show();
                            } else if (type === 1) {
                                // Color
                                $image.css({
                                    background: value
                                });
                                $image.show();
                            } else if (type === 0 || type === 3) {
                                // Default
                                $image.hide();
                            }

                            $title.text(label);

                            leftOpt = $this.offset().left;
                            left = leftOpt + $this.width() / 2 - $element.width() / 2;
                            $window = $(window);

                            // the numbers (5 and 5) is magick constants for offset from left or right page
                            if (left < 0) {
                                left = 5;
                            } else if (left + $element.width() > $window.width()) {
                                left = $window.width() - $element.width() - 5;
                            }

                            // the numbers (6,  3 and 18) is magick constants for offset tooltip
                            leftCorner = 0;

                            if ($element.width() < $this.width()) {
                                leftCorner = $element.width() / 2 - 3;
                            } else {
                                leftCorner = (leftOpt > left ? leftOpt - left : left - leftOpt) + $this.width() / 2 - 6;
                            }

                            $corner.css({
                                left: leftCorner
                            });
                            $element.css({
                                left: left,
                                top: $this.offset().top - $element.height() - $corner.height() - 18
                            }).show();
                        },
                        $widget.options.delay
                    );
                }
            }, function () {
                $element.hide();
                clearTimeout(timer);
            });
            $(document).on('tap', function () {
                $element.hide();
                clearTimeout(timer);
            });

            $this.on('tap', function (event) {
                event.stopPropagation();
            });
        }
    });

    /**
     * Render swatch controls with options and use tooltips.
     * Required two json:
     *  - jsonConfig (magento's option config)
     *  - jsonSwatchConfig (swatch's option config)
     *
     *  Tuning:
     *  - numberToShow (show "more" button if options are more)
     *  - onlySwatches (hide selectboxes)
     *  - moreButtonText (text for "more" button)
     *  - selectorProduct (selector for product container)
     *  - selectorProductPrice (selector for change price)
     */
    $.widget('mage.SwatchRenderer', {
        options: {
            classes: {
                attributeClass: 'swatch-attribute',
                attributeLabelClass: 'swatch-attribute-label',
                attributeSelectedOptionLabelClass: 'swatch-attribute-selected-option',
                attributeOptionsWrapper: 'swatch-attribute-options',
                attributeInput: 'swatch-input',
                optionClass: 'swatch-option',
                selectClass: 'swatch-select',
                moreButton: 'swatch-more',
                loader: 'swatch-option-loading'
            },
            // option's json config
            jsonConfig: {},

            // swatch's json config
            jsonSwatchConfig: {},

            // selector of parental block of prices and swatches (need to know where to seek for price block)
            selectorProduct: '.product-info-main',

            // selector of price wrapper (need to know where set price)
            selectorProductPrice: '[data-role=priceBox]',

            //selector of product images gallery wrapper
            mediaGallerySelector: '[data-gallery-role=gallery-placeholder]',

            // selector of category product tile wrapper
            selectorProductTile: '.product-item',

            // number of controls to show (false or zero = show all)
            numberToShow: false,

            // show only swatch controls
            onlySwatches: false,

            // enable label for control
            enableControlLabel: true,

            // text for more button
            moreButtonText: 'More',

            // Callback url for media
            mediaCallback: '',

            // Local media cache
            mediaCache: {},

            // Cache for BaseProduct images. Needed when option unset
            mediaGalleryInitial: [{}],

            // whether swatches are rendered in product list or on product page
            inProductList: false,

            /**
             * Defines the mechanism of how images of a gallery should be
             * updated when user switches between configurations of a product.
             *
             * As for now value of this option can be either 'replace' or 'prepend'.
             *
             * @type {String}
             */
            gallerySwitchStrategy: 'replace',

            // sly-old-price block selector
            slyOldPriceSelector: '.sly-old-price',

            fomatPrice: {},

            currencyRate: 1
        },

        /**
         * Get chosen product
         *
         * @returns array
         */
        getProduct: function () {
            return this._CalcProducts().shift();
        },

        /**
         * @private
         */
        _init: function () {
            if (this.options.jsonConfig !== '' && this.options.jsonSwatchConfig !== '') {
                this._sortAttributes();
                this._RenderControls();
            } else {
                console.log('SwatchRenderer: No input data received');
            }
        },

        /**
         * @private
         */
        _sortAttributes: function () {
            this.options.jsonConfig.attributes = _.sortBy(this.options.jsonConfig.attributes, function (attribute) {
                return attribute.position;
            });
        },

        /**
         * @private
         */
        _create: function () {
            var options = this.options,
                gallery = $('[data-gallery-role=gallery-placeholder]', '.column.main'),
                isProductViewExist = $('body.catalog-product-view').size() > 0,
                $main = isProductViewExist ?
                    this.element.parents('.column.main') :
                    this.element.parents('.product-item-info');

            if (isProductViewExist) {
                gallery.data('gallery') ?
                    this._onGalleryLoaded(gallery) :
                    gallery.on('gallery:loaded', this._onGalleryLoaded.bind(this, gallery));
            } else {
                options.mediaGalleryInitial = [{
                    'img': $main.find('.product-image-photo').attr('src')
                }];
            }
            this.productForm = this.element.parents(this.options.selectorProductTile).find('form:first');
            this.inProductList = this.productForm.length > 0;
        },

        /**
         * Render controls
         *
         * @private
         */
        _RenderControls: function () {
            var $widget = this,
                container = this.element,
                classes = this.options.classes,
                chooseText = this.options.jsonConfig.chooseText,
                currentCurrency = $('head meta[property="product:price:currency"]').attr('content');

            //bss
            this.options.currencyRate = $widget.options.jsonModuleConfig['currency_rate'][currentCurrency];
            $widget.optionsMap = {};

            $.each(this.options.jsonConfig.attributes, function () {
                var item = this,
                    options = $widget._RenderSwatchOptions(item),
                    select = $widget._RenderSwatchSelect(item, chooseText),
                    input = $widget._RenderFormInput(item),
                    label = '';

                // Show only swatch controls
                if ($widget.options.onlySwatches && !$widget.options.jsonSwatchConfig.hasOwnProperty(item.id)) {
                    return;
                }

                if ($widget.options.enableControlLabel) {
                    label +=
                        '<span class="' + classes.attributeLabelClass + '">' + item.label + '</span>' +
                        '<span class="' + classes.attributeSelectedOptionLabelClass + '"></span>';
                }

                if ($widget.inProductList) {
                    $widget.productForm.append(input);
                    input = '';
                }

                // Create new control
                container.append(
                    '<div class="' + classes.attributeClass + ' ' + item.code +
                        '" attribute-code="' + item.code +
                        '" attribute-id="' + item.id + '">' +
                            label +
                        '<div class="' + classes.attributeOptionsWrapper + ' clearfix">' +
                            options + select +
                        '</div>' + input +
                    '</div>'
                );

                $widget.optionsMap[item.id] = {};

                // Aggregate options array to hash (key => value)
                $.each(item.options, function () {
                    if (this.products.length > 0) {
                        $widget.optionsMap[item.id][this.id] = {
                            price: parseInt(
                                $widget.options.jsonConfig.optionPrices[this.products[0]].finalPrice.amount,
                                10
                            ),
                            products: this.products
                        };
                    }
                });
            });

            // Connect Tooltip
            container
                .find('[option-type="1"], [option-type="2"], [option-type="0"], [option-type="3"]')
                .SwatchRendererTooltip();

            // Hide all elements below more button
            $('.' + classes.moreButton).nextAll().hide();

            // Handle events like click or change
            $widget._EventListener();

            // Rewind options
            $widget._Rewind(container);

            //Emulate click on all swatches from Request
            $widget._EmulateSelected($.parseQuery());
            $widget._EmulateSelected($widget._getSelectedAttributes());
            //bss
            $widget._UpdateSelected($widget.options, $widget);
            $widget._UpdatePrice();
        },

        /**
         * Render swatch options by part of config
         *
         * @param {Object} config
         * @returns {String}
         * @private
         */
        _RenderSwatchOptions: function (config) {
            var optionConfig = this.options.jsonSwatchConfig[config.id],
                optionClass = this.options.classes.optionClass,
                moreLimit = parseInt(this.options.numberToShow, 10),
                moreClass = this.options.classes.moreButton,
                moreText = this.options.moreButtonText,
                countAttributes = 0,
                html = '';

            if (!this.options.jsonSwatchConfig.hasOwnProperty(config.id)) {
                return '';
            }

            $.each(config.options, function () {
                var id,
                    type,
                    value,
                    thumb,
                    label,
                    attr;

                if (!optionConfig.hasOwnProperty(this.id)) {
                    return '';
                }

                // Add more button
                if (moreLimit === countAttributes++) {
                    html += '<a href="#" class="' + moreClass + '">' + moreText + '</a>';
                }

                id = this.id;
                type = parseInt(optionConfig[id].type, 10);
                value = optionConfig[id].hasOwnProperty('value') ? optionConfig[id].value : '';
                thumb = optionConfig[id].hasOwnProperty('thumb') ? optionConfig[id].thumb : '';
                label = this.label ? this.label : '';
                attr =
                    ' option-type="' + type + '"' +
                    ' option-id="' + id + '"' +
                    ' option-label="' + label + '"' +
                    ' option-tooltip-thumb="' + thumb + '"' +
                    ' option-tooltip-value="' + value + '"';

                if (!this.hasOwnProperty('products') || this.products.length <= 0) {
                    attr += ' option-empty="true"';
                }

                if (type === 0) {
                    // Text
                    html += '<div class="' + optionClass + ' text" ' + attr + '>' + (value ? value : label) +
                        '</div>';
                } else if (type === 1) {
                    // Color
                    html += '<div class="' + optionClass + ' color" ' + attr +
                        '" style="background: ' + value +
                        ' no-repeat center; background-size: initial;">' + '' +
                        '</div>';
                } else if (type === 2) {
                    // Image
                    html += '<div class="' + optionClass + ' image" ' + attr +
                        '" style="background: url(' + value + ') no-repeat center; background-size: initial;">' + '' +
                        '</div>';
                } else if (type === 3) {
                    // Clear
                    html += '<div class="' + optionClass + '" ' + attr + '></div>';
                } else {
                    // Defaualt
                    html += '<div class="' + optionClass + '" ' + attr + '>' + label + '</div>';
                }
            });

            return html;
        },

        /**
         * Render select by part of config
         *
         * @param {Object} config
         * @param {String} chooseText
         * @returns {String}
         * @private
         */
        _RenderSwatchSelect: function (config, chooseText) {
            var html;

            if (this.options.jsonSwatchConfig.hasOwnProperty(config.id)) {
                return '';
            }

            html =
                '<select class="' + this.options.classes.selectClass + ' ' + config.code + '">' +
                '<option value="0" option-id="0">' + chooseText + '</option>';

            $.each(config.options, function () {
                var label = this.label,
                    attr = ' value="' + this.id + '" option-id="' + this.id + '"';

                if (!this.hasOwnProperty('products') || this.products.length <= 0) {
                    attr += ' option-empty="true"';
                }

                html += '<option ' + attr + '>' + label + '</option>';
            });

            html += '</select>';

            return html;
        },

        /**
         * Input for submit form.
         * This control shouldn't have "type=hidden", "display: none" for validation work :(
         *
         * @param {Object} config
         * @private
         */
        _RenderFormInput: function (config) {
            return '<input class="' + this.options.classes.attributeInput + ' super-attribute-select" ' +
                'name="super_attribute[' + config.id + ']" ' +
                'type="text" ' +
                'value="" ' +
                'data-selector="super_attribute[' + config.id + ']" ' +
                'data-validate="{required:true}" ' +
                'aria-required="true" ' +
                'aria-invalid="true" ' +
                'style="visibility: hidden; position:absolute; left:-1000px">';
        },

        /**
         * Event listener
         *
         * @private
         */
        _EventListener: function () {

            var $widget = this;

            $widget.element.on('click', '.' + this.options.classes.optionClass, function () {
                return $widget._OnClick($(this), $widget);
            });

            $widget.element.on('change', '.' + this.options.classes.selectClass, function () {
                return $widget._OnChange($(this), $widget);
            });

            $widget.element.on('click', '.' + this.options.classes.moreButton, function (e) {
                e.preventDefault();

                return $widget._OnMoreClick($(this));
            });
            //bss
            $widget._ValidateQty($widget);
        },

        /**
         * Event for swatch options
         *
         * @param {Object} $this
         * @param {Object} $widget
         * @private
         */
        _OnClick: function ($this, $widget) {
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                $label = $parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
                attributeId = $parent.attr('attribute-id'),
                $input = $parent.find('.' + $widget.options.classes.attributeInput);

            if ($widget.inProductList) {
                $input = $widget.productForm.find(
                    '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                );
            }

            if ($this.hasClass('disabled')) {
                return;
            }

            if ($this.hasClass('selected')) {
                $parent.removeAttr('option-selected').find('.selected').removeClass('selected');
                $input.val('');
                $label.text('');
                //bss_commerce
                $widget._ResetDetail();
            } else {
                $parent.attr('option-selected', $this.attr('option-id')).find('.selected').removeClass('selected');
                $label.text($this.attr('option-label'));
                $input.val($this.attr('option-id'));
                $this.addClass('selected');
            }

            $widget._Rebuild();
            //bss
            if ($widget.element.parents($widget.options.selectorProduct)
                    .find(this.options.selectorProductPrice).is(':data(mage-priceBox)')
            ) {
                $widget._UpdatePrice();
            }
            $widget._UpdateDetail();
            $input.trigger('change');
        },

        /**
         * Event for select
         *
         * @param {Object} $this
         * @param {Object} $widget
         * @private
         */
        _OnChange: function ($this, $widget) {
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                attributeId = $parent.attr('attribute-id'),
                $input = $parent.find('.' + $widget.options.classes.attributeInput);

            if ($widget.inProductList) {
                $input = $widget.productForm.find(
                    '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                );
            }

            if ($this.val() > 0) {
                $parent.attr('option-selected', $this.val());
                $input.val($this.val());
            } else {
                $parent.removeAttr('option-selected');
                $input.val('');
            }

            $widget._Rebuild();
            //bss
            $widget._UpdatePrice();
            $widget._UpdateDetail();
            $input.trigger('change');
        },

        /**
         * Event for more switcher
         *
         * @param {Object} $this
         * @private
         */
        _OnMoreClick: function ($this) {
            $this.nextAll().show();
            $this.blur().remove();
        },

        /**
         * Rewind options for controls
         *
         * @private
         */
        _Rewind: function (controls) {
            controls.find('div[option-id], option[option-id]').removeClass('disabled').removeAttr('disabled');
            controls.find('div[option-empty], option[option-empty]').attr('disabled', true).addClass('disabled');
        },
        /**
         * Bss_commerce
         * Update Sku
         *
         */
        _UpdateDetail: function () {
            var $widget = this,
                index = '',
                childProductData = this.options.jsonChildProduct,
                moduleConfig = this.options.jsonModuleConfig,
                keymap,
                url = '';
            $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                index += $(this).attr('option-selected') + '_';
                url += '+' + $(this).attr('attribute-code') + '-'
                + $widget.options.jsonChildProduct['map'][$(this).attr('attribute-id')][$(this).attr('option-selected')];
            });
            if (!childProductData['child'].hasOwnProperty(index)) {
                $widget._ResetDetail();
                return false;
            }
            $widget._UpdateUrl(
                childProductData['url'],
                url,
                moduleConfig['url']
            );
            $widget._UpdateSku(childProductData['child'][index]['sku'], moduleConfig['sku']);
            
            $widget._UpdateName(childProductData['child'][index]['name'], moduleConfig['name']);

            $widget._UpdateDesc(
                childProductData['child'][index]['desc'],
                childProductData['child'][index]['sdesc'],
                moduleConfig['desc']
            );
            $widget._UpdateStock(
                childProductData['child'][index]['stock_status'],
                childProductData['child'][index]['stock_number'],
                moduleConfig['stock']
            );
            $widget._UpdateTierPrice(
                childProductData['child'][index]['price']['tier_price'],
                childProductData['child'][index]['price']['basePrice'],
                $widget.options.currencyRate,
                moduleConfig,
                childProductData['child'][index]['tax'],
                childProductData['child'][index]['same_rate_as_store']
            );
            $widget._UpdateIncrement(
                childProductData['child'][index]['increment'],
                childProductData['child'][index]['name'],
                moduleConfig['increment']
            );
            $widget._UpdateMinQty(
                childProductData['child'][index]['minqty'],
                moduleConfig['min_max']
            );
            $widget._UpdateImage(
                childProductData['child'][index]['image'],
                moduleConfig['images']
            );
        },
        _UpdateSku: function ($sku, $config) {
            if ($config > 0) {
                $('.product.attribute.sku .value').html($sku);
            }
        },
        _UpdateName: function ($name, $config) {
            if ($config > 0) {
                $('.page-title .base').html($name);
            }
        },
        _UpdateDesc: function ($desc, $sdesc, $config) {
            if ($config > 0) {
                this._UpdateFullDesc($desc);
                this._UpdateShortDesc($sdesc);
            }
        },
        _UpdateFullDesc: function ($desc) {
            var html;
            if ($desc) {
                if ($('#tab-label-product\\.info\\.description').css('display') != 'none') {
                    $('.product.attribute.description .value').html($desc);
                } else {
                    $('.data.item.title').removeClass("active");
                    $('.data.item.content').css('display', 'none');
                    $('#tab-label-product\\.info\\.description').css('display', 'inline-block').addClass("active");
                    $('#product\\.info\\.description').css('display', 'block');
                    $('.product.attribute.description .value').html($desc);
                }
            } else {
                $('#tab-label-product\\.info\\.description').css('display', 'none').removeClass("active");
                $('#product\\.info\\.description').css('display', 'none');
                $('.product.data.items').children('.data.item.title').eq(1).addClass("active");
                $('.product.data.items').children('.data.item.content').eq(1).css('display', 'inline-block');
            }
        },
        _UpdateShortDesc: function ($sdesc) {
            var html;
            if ($sdesc) {
                if ($('.product.attribute.overview .value').length) {
                    $('.product.attribute.overview .value').html($sdesc);
                } else {
                    html = '<div class="product attribute overview">'
                    + '<div class="value" itemprop="description">'
                    + $sdesc
                    + '</div></div>';
                    $('.product-social-links').after(html);
                }
            } else {
                $('.product.attribute.overview').remove();
            }
        },
        _UpdateStock: function ($status, $number, $config) {
            if ($config > 0) {
                var stock_status = '';
                if ($status > 0) {
                    stock_status = $t('IN STOCK');
                    $('#product-addtocart-button').removeAttr('disabled');
                } else {
                    stock_status = $t('OUT OF STOCK');
                    $('#product-addtocart-button').attr('disabled', 'disabled');
                }
                stock_status += " - " + Number($number);
                $('.stock.available span').html(stock_status);
            }
        },
        _UpdateIncrement: function ($increment, $name, $config) {
            $(".product.pricing").remove();
            if ($config > 0 && $increment > 0) {
                var html = '<div class="product pricing">';
                html += $t('%1 is available to buy in increments of %2').replace('%1', $name).replace('%2', $increment);
                html += '</div>';
                $('.product-social-links').after(html);
            }
        },
        _UpdateMinQty: function ($value, $config) {
            if ($config > 0) {
                if ($value > 0) {
                    $('input.input-text.qty').val($value);
                    $('input.input-text.qty').trigger('change');
                } else {
                    $('input.input-text.qty').val(1);
                    $('input.input-text.qty').trigger('change');
                }
            }
        },
        _UpdateTierPrice: function ($priceData, $basePrice, $rate, $moduleConfig, $tax, $sameRateAsStore) {
            if ($moduleConfig['tier_price'] > 0) {
                var $widget = this,
                    valueAfterTax,
                    taxRate,
                    tier_price_1,
                    tier_price_2,
                    base_price = $basePrice,
                    percent,
                    tier_number_1,
                    tier_number_2,
                    tier_number = 0,
                    html = '',
                    htmlTierPrice = '',
                    have_tier_price = false,
                    htmlTierPrice4 = '<span class="percent tier-%4">&nbsp;%5</span>%</strong>',
                    htmlTierPrice5 = '<span class="price-container price-tier_price tax weee"><span data-price-amount="%2" data-price-type="" class="price-wrapper "><span class="price">%3</span></span></span>';
                $(".prices-tier.items").remove();
                html = '<ul class="prices-tier items">';
                $.each($priceData, function (key, vl) {
                    
                    valueAfterTax = Number(vl['value']);
                    taxRate = Number($tax)/100;
                    if ($moduleConfig['tax_based_on'] !== 'origin') {
                        if ($moduleConfig['tax'] == '1') {
                            if ($moduleConfig['catalog_price_include_tax'] > 0 && $sameRateAsStore) {
                                valueAfterTax = valueAfterTax - valueAfterTax * (1 - 1/(1 + taxRate));
                            }
                        } else {
                            if ($moduleConfig['catalog_price_include_tax'] == 0 || !$sameRateAsStore) {
                                valueAfterTax += valueAfterTax * taxRate;
                            }
                        }
                    }
                    percent = Math.round((1 - Number(vl['value'])/Number(base_price)) * 100);
                    if (percent == 0) {
                        percent = ((1 - Number(vl['value'])/Number(base_price)) * 100).toFixed(2);
                    }

                    have_tier_price = true;
                    htmlTierPrice = $t('Buy %1 for ').replace('%1', Number(vl['qty']));
                    htmlTierPrice += htmlTierPrice5.replace('%2', valueAfterTax).replace('%3', $widget._getFormattedPrice(Number(valueAfterTax * Number($rate))));
                    htmlTierPrice += $t(' each and ');
                    htmlTierPrice += '<strong class="benefit">';
                    htmlTierPrice += $t('save');
                    htmlTierPrice += htmlTierPrice4.replace('%4', key).replace('%5', percent);
                    html += '<li class="item">';
                    html += htmlTierPrice;
                    html += '</li>';
                });
                html += '</ul>';
                if (have_tier_price) {
                    $('.product-info-price').after(html);
                }
            }
        },
        _UpdateImage: function (images, $config) {
            
                var justAnImage = images[0],
                    updateImg,
                    $this = this.element,
                    imagesToUpdate,
                    isProductViewExist = $('body.catalog-product-view').size() > 0,
                    context = isProductViewExist ? $this.parents('.column.main') : $this.parents('.product-item-info'),
                    gallery = context.find(this.options.mediaGallerySelector).data('gallery'),
                    item;
            if ($config < 1) {
                if (this.options.onlyMainImg) {
                    var widget = this;
                    $.each(images, function ($id, $vl) {
                        if ($vl.isMain) {
                            imagesToUpdate = widget.options.jsonChildProduct['image'];
                            imagesToUpdate[0] = $vl;
                            return true;
                        }
                    });
                    images = imagesToUpdate;
                } else {
                    images = images.concat(this.options.jsonChildProduct['image']);
                }
            }
            if (isProductViewExist) {
                imagesToUpdate = images.length ? this._setImageType($.extend(true, [], images)) : [];
                gallery.updateData(images);
                gallery.first();
            } else if (justAnImage && justAnImage.img) {
                context.find('.product-image-photo').attr('src', justAnImage.img);
            }
        },
        _UpdateSelected: function ($options, $widget) {
            var config = $options.jsonModuleConfig,
            data = $options.jsonChildProduct,
            customUrl = window.location.pathname,
            selectingAttr = [],
            attr,
            selectedAttr = customUrl.split('+'),
            flag = false;
            selectedAttr.shift();
            if (config['url'] > 0 && selectedAttr.length > 0) {
                flag = true;
                $.each(selectedAttr, function ($index, $vl) {
                    if (typeof $vl === 'string') {
                        attr = $vl.split('-');
                        while (attr[1].indexOf('~') >= 0) {
                            attr[1] = attr[1].replace("~", " ");
                        }
                        try {
                            if ($('.swatch-attribute[attribute-code="'
                                + attr[0]
                                + '"] .swatch-attribute-options').children().is('div')) {
                                $('.swatch-attribute[attribute-code="'
                                + attr[0]
                                + '"] .swatch-attribute-options [option-label="'
                                + attr[1]
                                + '"]').trigger('click');
                            } else {
                                $.each($('.swatch-attribute[attribute-code="'
                                + attr[0]
                                + '"] .swatch-attribute-options select option'), function ($index2, $vl2) {
                                    if ($vl2.text == decodeURIComponent(attr[1])) {
                                        $('.swatch-attribute[attribute-code="'
                                        + attr[0]
                                        + '"] .swatch-attribute-options select').val($vl2.value).trigger('change');
                                        return true;
                                    }
                                });
                            }
                        } catch (e) {
                        }
                    }
                });
            } else {
                if (config['preselect'] > 0 && data['preselect']['enabled'] > 0) {
                    flag = true;
                    $.each(data['preselect']['data'], function ($index, $vl) {
                        try {
                            if ($('.swatch-attribute[attribute-id='
                                + $index
                                + '] .swatch-attribute-options').children().is('div')) {
                                $('.swatch-attribute[attribute-id='
                                + $index
                                + '] .swatch-attribute-options [option-id='
                                + $vl
                                + ']').trigger('click');
                            } else {
                                $('.swatch-attribute[attribute-id='
                                + $index
                                + '] .swatch-attribute-options select').val($vl).trigger('change');
                            }
                        } catch (e) {
                        }
                    });
                }
            }
            if (flag) {
                $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                    index += $(this).attr('option-selected') + '_';
                });
                var justAnImage,
                    images,
                    imagesToUpdate,
                    isProductViewExist = $('body.catalog-product-view').size() > 0,
                    context = isProductViewExist ? $widget.element.parents('.column.main') : $widget.element.parents('.product-item-info'),
                    gallery = context.find($widget.options.mediaGallerySelector),
                    item, keymap, index = '';
                $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                    index += $(this).attr('option-selected') + '_';
                });
                images = data['child'][index]['image'];
                if (config['images'] > 0) {
                    justAnImage = images[0];
                    if (isProductViewExist) {
                        gallery.on('gallery:loaded', function () {
                            imagesToUpdate = images.length ? $widget._setImageType($.extend(true, [], images)) : [];
                            gallery.data('gallery').updateData(images);
                        });
                    } else if (justAnImage && justAnImage.img) {
                        context.find('.product-image-photo').attr('src', justAnImage.img);
                    }
                } else {
                    if ($widget.options.onlyMainImg) {
                        $.each(images, function ($id, $vl) {
                            if ($vl.isMain) {
                                imagesToUpdate = $widget.options.jsonChildProduct['image'];
                                imagesToUpdate[0] = $vl;
                                return true;
                            }
                        });
                        images = imagesToUpdate;
                    } else {
                        images = images.concat($widget.options.jsonChildProduct['image']);
                    }
                    if (isProductViewExist) {
                        gallery.on('gallery:loaded', function () {
                            imagesToUpdate = images.length ? $widget._setImageType($.extend(true, [], images)) : [];
                            gallery.data('gallery').updateData(images);
                            gallery.data('gallery').first();
                        });
                    } else if (justAnImage && justAnImage.img) {
                        context.find('.product-image-photo').attr('src', justAnImage.img);
                    }
                }
            }
        },
        _UpdateUrl: function ($parentUrl, $customUrl, $config) {
            if ($config > 0) {
                while ($customUrl.indexOf(' ') >= 0) {
                    $customUrl = $customUrl.replace(" ", "~");
                }
                if ($parentUrl.substring($parentUrl.lastIndexOf('.')) == ".html") {
                    $parentUrl = $parentUrl.substring(0, $parentUrl.lastIndexOf('.'));
                }
                var url = $parentUrl + $customUrl
                window.history.replaceState('SDCP', 'SCDP', url);
            }
        },
        _ValidateQty: function ($widget) {
            var keymap, index,
            data = $widget.options.jsonChildProduct,
            config = $widget.options.jsonModuleConfig,
            state;
            $('input.input-text.qty').change(function () {
                index = '';
                $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                    index += $(this).attr('option-selected') + '_';
                });
                if (data['child'].hasOwnProperty(index) && data['child'][index]['stock_status'] > 0) {
                    state = data['child'][index]['stock_status'] > 0;
                    if (config['min_max'] > 0) {
                        state = state && (data['child'][index]['minqty'] == 0 || $(this).val() >= data['child'][index]['minqty'])
                        && (data['child'][index]['maxqty'] == 0 || $(this).val() <= data['child'][index]['maxqty']);
                    }
                    if (config['increment'] > 0) {
                        state = state && (data['child'][index]['increment'] == 0 || $(this).val() % data['child'][index]['increment'] == 0);
                    }
                    if (!state) {
                        $('#product-addtocart-button').attr('disabled', 'disabled');
                    } else {
                        $('#product-addtocart-button').removeAttr('disabled');
                    }
                }
            });
        },
        _ResetDetail: function () {
            var moduleConfig = this.options.jsonModuleConfig;
            this._ResetSku(moduleConfig['sku']);
            this._ResetName(moduleConfig['name']);
            this._ResetDesc(moduleConfig['desc']);
            this._ResetStock(moduleConfig['stock']);
            this._ResetTierPrice(moduleConfig['tier_price']);
            this._ResetUrl(moduleConfig['url']);
            this._ResetIncrement(moduleConfig['increment']);
            this._ResetImage(moduleConfig['images']);
        },
        _ResetSku: function ($config) {
            if ($config > 0) {
                $('.product.attribute.sku .value').html(this.options.jsonChildProduct['sku']);
            }
        },
        _ResetName: function ($config) {
            if ($config > 0) {
                $('.page-title .base').html(this.options.jsonChildProduct['name']);
            }
        },
        _ResetDesc: function ($config) {
            if ($config > 0) {
                $('.product.attribute.description .value').html(this.options.jsonChildProduct['desc']);
                $('.product.attribute.overview .value').html(this.options.jsonChildProduct['sdesc']);
            }
        },
        _ResetStock: function ($config) {
            if ($config > 0) {
                var stock_status = '';
                if (this.options.jsonChildProduct['stock_status'] > 0) {
                    stock_status = $t('IN STOCK');
                    $('.price-box.price-final_price').css('display', 'block');
                    $('#product-addtocart-button').removeAttr('disabled');
                } else {
                    stock_status = $t('OUT OF STOCK');
                    $('#product-addtocart-button').attr('disabled', 'disabled');
                }
                $('.stock.available span').html(stock_status);
            }
        },
        _ResetTierPrice: function ($config) {
            if ($config > 0) {
                $(".prices-tier.items").remove();
            }
        },
        _ResetIncrement: function ($config) {
            if ($config > 0) {
                $(".product.pricing").remove();
            }
        },
        _ResetImage: function ($config) {
            var images = this.options.jsonChildProduct['image'],
                justAnImage = images[0],
                updateImg,
                $this = this.element,
                imagesToUpdate,
                isProductViewExist = $('body.catalog-product-view').size() > 0,
                context = isProductViewExist ? $this.parents('.column.main') : $this.parents('.product-item-info'),
                gallery = context.find(this.options.mediaGallerySelector).data('gallery'),
                item;
            if (isProductViewExist) {
                imagesToUpdate = images.length ? this._setImageType($.extend(true, [], images)) : [];
                gallery.updateData(images);
            } else if (justAnImage && justAnImage.img) {
                context.find('.product-image-photo').attr('src', justAnImage.img);
            }
        },
        _ResetUrl: function ($config) {
            if ($config > 0) {
                window.history.replaceState(null, null, this.options.jsonChildProduct['url']);
            }
        },
        /**
         * Rebuild container
         *
         * @private
         */
        _Rebuild: function () {
            var $widget = this,
                productIndex = Object.keys(this.options.jsonChildProduct['child']),
                controls = $widget.element.find('.' + $widget.options.classes.attributeClass + '[attribute-id]'),
                selected = controls.filter('[option-selected]'),
                SDCP_controls = $widget.element.find('.swatch-attribute[attribute-id]'),
                SDCP_not_selected = $widget.element.find('.swatch-attribute[attribute-id]').not('[option-selected]'),
                SDCP_selected = $widget.element.find('.swatch-attribute[attribute-id]').filter('[option-selected]');
            // Enable all options
            $widget._Rewind(controls);

            // done if nothing selected
            if (selected.size() <= 0) {
                return;
            }
            //bss
            if ($widget.options.jsonModuleConfig['stock'] < 1) {
            // Disable not available options
                controls.each(function () {
                    var $this = $(this),
                        id = $this.attr('attribute-id'),
                        products = $widget._CalcProducts(id);

                    if (selected.size() === 1 && selected.first().attr('attribute-id') === id) {
                        return;
                    }

                    $this.find('[option-id]').each(function () {
                        var $element = $(this),
                            option = $element.attr('option-id');

                        if (!$widget.optionsMap.hasOwnProperty(id) || !$widget.optionsMap[id].hasOwnProperty(option) ||
                            $element.hasClass('selected') ||
                            $element.is(':selected')) {
                            return;
                        }

                        if (_.intersection(products, $widget.optionsMap[id][option].products).length <= 0) {
                            $element.attr('disabled', true).addClass('disabled');
                        }
                    });
                });
            } else {
                var selectedKey = [],
                attributeList = [];
                SDCP_selected.each(function () {
                    selectedKey[$(this).attr('attribute-id')] = $(this).attr('option-selected');
                });
                SDCP_controls.each(function () {
                    attributeList.push($(this).attr('attribute-id'));
                });
                SDCP_controls.each(function ($index) {
                    var controlElem = $(this),
                    selectedIndexRemain = [],
                    selectedIndexRemainMap = [],
                    remainProductIndex;
                    $.each(attributeList, function ($id, $vl) {
                        if ($vl != controlElem.attr('attribute-id')) {
                            selectedIndexRemain.push(selectedKey[$vl]);
                            selectedIndexRemainMap[$id] = selectedKey[$vl];
                        }
                    });
                    if ($(this).children('.swatch-attribute-options').children().first().prop('tagName') == "DIV") {
                        $(this).children('.swatch-attribute-options').children().each(function () {
                            selectedIndexRemainMap[$index] = $(this).attr('option-id');
                            var fakeIndex = selectedIndexRemainMap.join('_') + '_';
                            if (fakeIndex.indexOf('__') < 0 && fakeIndex.indexOf('_') > 0
                                && productIndex.indexOf(fakeIndex) < 0) {
                                $(this).addClass('disabled');
                            }
                        });
                    } else {
                        var element = $(this);
                        $.each($widget.options.jsonChildProduct['map2'][$(this).attr('attribute-code')]['child'], function ($id, $vl) {
                            if (element.children('.swatch-attribute-options')
                            .children('select').find("[option-id=" + $id + "]").prop('tagName') != 'OPTION') {
                                element.children('.swatch-attribute-options').children('select').append(
                                    '<option value="' + $id + '" option-id="' + $id + '">' + $vl + '</option>'
                                );
                            }
                        });
                        $(this).children('.swatch-attribute-options')
                        .children('select').children('option').each(function () {
                            if ($(this).attr('option-id') != '0') {
                                selectedIndexRemainMap[$index] = $(this).attr('option-id');
                                var fakeIndex = selectedIndexRemainMap.join('_') + '_';
                                if (fakeIndex.indexOf('__') < 0 && fakeIndex.indexOf('_') > 0
                                    && productIndex.indexOf(fakeIndex) < 0) {
                                    $(this).remove();
                                }
                            }
                        });
                    }
                });
            }
        },

        /**
         * Get selected product list
         *
         * @returns {Array}
         * @private
         */
        _CalcProducts: function ($skipAttributeId) {
            var $widget = this,
                products = [];

            // Generate intersection of products
            $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                var id = $(this).attr('attribute-id'),
                    option = $(this).attr('option-selected');

                if ($skipAttributeId !== undefined && $skipAttributeId === id) {
                    return;
                }

                if (!$widget.optionsMap.hasOwnProperty(id) || !$widget.optionsMap[id].hasOwnProperty(option)) {
                    return;
                }

                if (products.length === 0) {
                    products = $widget.optionsMap[id][option].products;
                } else {
                    products = _.intersection(products, $widget.optionsMap[id][option].products);
                }
            });

            return products;
        },

        /**
         * Update total price
         *
         * @private
         */
        _UpdatePrice: function () {
            var $widget = this,
                index = '',
                $product = $widget.element.parents($widget.options.selectorProduct),
                $productPrice = $product.find(this.options.selectorProductPrice),
                options = _.object(_.keys($widget.optionsMap), {}),
                childData = $widget.options.jsonChildProduct['child'],
                result = {
                    oldPrice: {amount: ''},
                    basePrice: {amount: ''},
                    finalPrice: {amount: ''}
                };

            
            $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                index += $(this).attr('option-selected') + '_';
            });
            var $taxRate,
                $sameRateAsStore;
            if (childData.hasOwnProperty(index)) {
                $taxRate = Number(childData[index]['tax']) / 100;
                $sameRateAsStore = childData[index]['same_rate_as_store'];
                result.oldPrice.amount = Number(childData[index]['price']['basePrice']) * Number($widget.options.currencyRate);
                result.basePrice.amount = Number(childData[index]['price']['finalPrice']) * Number($widget.options.currencyRate);
                result.finalPrice.amount = Number(childData[index]['price']['finalPrice']) * Number($widget.options.currencyRate);
            } else {
                $taxRate = Number($widget.options.jsonChildProduct['tax']) / 100;
                $sameRateAsStore = $widget.options.jsonChildProduct['same_rate_as_store'];
                result.oldPrice.amount = Number($widget.options.jsonChildProduct['price']['basePrice']) * Number($widget.options.currencyRate);
                result.basePrice.amount = Number($widget.options.jsonChildProduct['price']['basePrice']) * Number($widget.options.currencyRate);
                result.finalPrice.amount = Number($widget.options.jsonChildProduct['price']['basePrice']) * Number($widget.options.currencyRate);
            }
            if ($widget.options.jsonModuleConfig['tax_based_on'] !== 'origin') {
                if ($widget.options.jsonModuleConfig['tax'] == '1') {
                    if ($widget.options.jsonModuleConfig['catalog_price_include_tax'] > 0 && $sameRateAsStore) {
                        result.basePrice.amount = result.finalPrice.amount - result.finalPrice.amount * (1 - 1/(1 + $taxRate));
                    }
                } else if ($widget.options.jsonModuleConfig['tax'] == '2') {
                    if ($widget.options.jsonModuleConfig['catalog_price_include_tax'] == 0 || !$sameRateAsStore) {
                        result.oldPrice.amount += result.oldPrice.amount * $taxRate;
                        result.finalPrice.amount += result.finalPrice.amount * $taxRate;
                    }
                } else {
                    if ($widget.options.jsonModuleConfig['catalog_price_include_tax'] > 0 && $sameRateAsStore) {
                        result.basePrice.amount = result.finalPrice.amount - result.finalPrice.amount * (1 - 1/(1 + $taxRate));
                    } else {
                        result.oldPrice.amount += result.oldPrice.amount * $taxRate;
                        result.finalPrice.amount += result.finalPrice.amount * $taxRate;
                    }
                }
            }
            $productPrice.trigger(
                'updatePrice',
                {
                    'prices': $widget._getPrices(result, $productPrice.priceBox('option').prices)
                }
            );
            if (result.oldPrice.amount !== result.finalPrice.amount) {
                $(this.options.slyOldPriceSelector).show();
            } else {
                $(this.options.slyOldPriceSelector).hide();
            }
        },

        /**
         * Get prices
         *
         * @param {Object} newPrices
         * @param {Object} displayPrices
         * @returns {*}
         * @private
         */
        _getPrices: function (newPrices, displayPrices) {
            var $widget = this;

            if (_.isEmpty(newPrices)) {
                newPrices = $widget.options.jsonConfig.prices;
            }

            _.each(displayPrices, function (price, code) {
                if (newPrices[code]) {
                    displayPrices[code].amount = newPrices[code].amount - displayPrices[code].amount;
                }
            });
            return displayPrices;
        },

        /**
         * Gets all product media and change current to the needed one
         *
         * @private
         */
        _LoadProductMedia: function () {
            var $widget = this,
                $this = $widget.element,
                attributes = {},
                productId = 0,
                mediaCallData,
                mediaCacheKey,

                /**
                 * Processes product media data
                 *
                 * @param {Object} data
                 * @returns void
                 */
                mediaSuccessCallback = function (data) {
                    if (!(mediaCacheKey in $widget.options.mediaCache)) {
                        $widget.options.mediaCache[mediaCacheKey] = data;
                    }
                    $widget._ProductMediaCallback($this, data);
                    $widget._DisableProductMediaLoader($this);
                };

            if (!$widget.options.mediaCallback) {
                return;
            }

            $this.find('[option-selected]').each(function () {
                var $selected = $(this);

                attributes[$selected.attr('attribute-code')] = $selected.attr('option-selected');
            });

            if ($('body.catalog-product-view').size() > 0) {
                //Product Page
                productId = document.getElementsByName('product')[0].value;
            } else {
                //Category View
                productId = $this.parents('.product.details.product-item-details')
                    .find('.price-box.price-final_price').attr('data-product-id');
            }

            mediaCallData = {
                'product_id': productId,
                'attributes': attributes,
                'additional': $.parseQuery()
            };
            mediaCacheKey = JSON.stringify(mediaCallData);

            if (mediaCacheKey in $widget.options.mediaCache) {
                mediaSuccessCallback($widget.options.mediaCache[mediaCacheKey]);
            } else {
                mediaCallData.isAjax = true;
                $widget._XhrKiller();
                $widget._EnableProductMediaLoader($this);
                $widget.xhr = $.post(
                    $widget.options.mediaCallback,
                    mediaCallData,
                    mediaSuccessCallback,
                    'json'
                ).done(function () {
                    $widget._XhrKiller();
                });
            }
        },

        /**
         * Enable loader
         *
         * @param {Object} $this
         * @private
         */
        _EnableProductMediaLoader: function ($this) {
            var $widget = this;

            if ($('body.catalog-product-view').size() > 0) {
                $this.parents('.column.main').find('.photo.image')
                    .addClass($widget.options.classes.loader);
            } else {
                //Category View
                $this.parents('.product-item-info').find('.product-image-photo')
                    .addClass($widget.options.classes.loader);
            }
        },

        /**
         * Disable loader
         *
         * @param {Object} $this
         * @private
         */
        _DisableProductMediaLoader: function ($this) {
            var $widget = this;

            if ($('body.catalog-product-view').size() > 0) {
                $this.parents('.column.main').find('.photo.image')
                    .removeClass($widget.options.classes.loader);
            } else {
                //Category View
                $this.parents('.product-item-info').find('.product-image-photo')
                    .removeClass($widget.options.classes.loader);
            }
        },

        /**
         * Callback for product media
         *
         * @param {Object} $this
         * @param {String} response
         * @private
         */
        _ProductMediaCallback: function ($this, response) {
            var isProductViewExist = $('body.catalog-product-view').size() > 0,
                $main = isProductViewExist ? $this.parents('.column.main') : $this.parents('.product-item-info'),
                $widget = this,
                images = [],

                /**
                 * Check whether object supported or not
                 *
                 * @param {Object} e
                 * @returns {*|Boolean}
                 */
                support = function (e) {
                    return e.hasOwnProperty('large') && e.hasOwnProperty('medium') && e.hasOwnProperty('small');
                };

            if (_.size($widget) < 1 || !support(response)) {
                this.updateBaseImage(this.options.mediaGalleryInitial, $main, isProductViewExist);

                return;
            }

            images.push({
                full: response.large,
                img: response.medium,
                thumb: response.small,
                isMain: true
            });

            if (response.hasOwnProperty('gallery')) {
                $.each(response.gallery, function () {
                    if (!support(this) || response.large === this.large) {
                        return;
                    }
                    images.push({
                        full: this.large,
                        img: this.medium,
                        thumb: this.small
                    });
                });
            }

            this.updateBaseImage(images, $main, isProductViewExist);
        },

        /**
         * Check if images to update are initial and set their type
         * @param {Array} images
         */
        _setImageType: function (images) {
            var initial = this.options.mediaGalleryInitial[0].img;

            if (images[0].img === initial) {
                images = $.extend(true, [], this.options.mediaGalleryInitial);
            } else {
                images.map(function (img) {
                    img.type = 'image';
                });
            }

            return images;
        },

        /**
         * Update [gallery-placeholder] or [product-image-photo]
         * @param {Array} images
         * @param {jQuery} context
         * @param {Boolean} isProductViewExist
         */
        updateBaseImage: function (images, context, isProductViewExist) {
            var justAnImage = images[0],
                initialImages = this.options.mediaGalleryInitial,
                gallery = context.find(this.options.mediaGallerySelector).data('gallery'),
                imagesToUpdate,
                updateImg,
                item,
                isInitial,
                $widget = this,
                keymap,
                index = '',
                childProductData = this.options.jsonChildProduct;
            $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                keymap = childProductData[$(this).attr('attribute-code')][$(this).attr('option-selected')];
                index += keymap + '_';
            });
            
            if (isProductViewExist) {
                imagesToUpdate = images.length ? this._setImageType($.extend(true, [], images)) : [];
                if (this.options.onlyMainImg) {
                    updateImg = imagesToUpdate.filter(function (img) {
                        return img.isMain;
                    });
                    item = updateImg.length ? updateImg[0] : imagesToUpdate[0];
                    gallery.updateDataByIndex(0, item);

                    gallery.seek(1);
                } else {
                    isInitial = _.isEqual(imagesToUpdate, initialImages);
                    if (this.options.gallerySwitchStrategy === 'prepend' && !isInitial) {
                        imagesToUpdate = imagesToUpdate.concat(initialImages);
                    }

                    gallery.updateData(imagesToUpdate);

                    if (isInitial) {
                        $(this.options.mediaGallerySelector).AddFotoramaVideoEvents();
                    }

                    gallery.first();
                }
            } else if (justAnImage && justAnImage.img) {
                context.find('.product-image-photo').attr('src', justAnImage.img);
            }
            if (!childProductData['child'].hasOwnProperty(index)) {
                $widget._ResetImage(1);
            }
        },

        /**
         * Kill doubled AJAX requests
         *
         * @private
         */
        _XhrKiller: function () {
            var $widget = this;

            if ($widget.xhr !== undefined && $widget.xhr !== null) {
                $widget.xhr.abort();
                $widget.xhr = null;
            }
        },

        /**
         * Emulate mouse click on all swatches that should be selected
         * @param {Object} [selectedAttributes]
         * @private
         */
        _EmulateSelected: function (selectedAttributes) {
            $.each(selectedAttributes, $.proxy(function (attributeCode, optionId) {
                this.element.find('.' + this.options.classes.attributeClass +
                    '[attribute-code="' + attributeCode + '"] [option-id="' + optionId + '"]').trigger('click');
            }, this));
        },

        /**
         * Get default options values settings with either URL query parameters
         * @private
         */
        _getSelectedAttributes: function () {
            var hashIndex = window.location.href.indexOf('#'),
                selectedAttributes = {},
                params;

            if (hashIndex !== -1) {
                params = $.parseQuery(window.location.href.substr(hashIndex + 1));

                selectedAttributes = _.invert(_.mapObject(_.invert(params), function (attributeId) {
                    var attribute = this.options.jsonConfig.attributes[attributeId];

                    return attribute ? attribute.code : attributeId;
                }.bind(this)));
            }

            return selectedAttributes;
        },

        /**
         * Callback which fired after gallery gets initialized.
         *
         * @param {HTMLElement} element - DOM element associated with a gallery.
         */
        _onGalleryLoaded: function (element) {
            var galleryObject = element.data('gallery');

            this.options.mediaGalleryInitial = galleryObject.returnCurrentImages();
        },

        _getFormattedPrice: function (price) {
            return priceUtils.formatPrice(price, this.options.fomatPrice);
        }
    });

    return $.mage.SwatchRenderer;
});
