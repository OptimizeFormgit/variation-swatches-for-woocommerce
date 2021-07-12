<?php 


class VSWC_Settings_Page {
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
    }

    public function admin_scripts() {
        if ( isset( $_GET['page'] ) && $_GET['page'] === 'variation-swatches-settings' ) {

            wp_enqueue_script( 'tawcvs-admin', plugins_url( '/assets/js/admin.js', dirname( __FILE__ ) ), array('jquery'), null, true );
            wp_enqueue_style( 'tawcvs-admin', plugins_url( '/assets/css/admin.css', dirname( __FILE__ ) ), array( 'wp-color-picker' ), null );
            wp_enqueue_style( 'wp-color-picker' ); 
        }
    }

    public function admin_menu() {
        add_menu_page( __('Variation Swatches', 'wcvs'), __('Variation Swatches', 'wcvs'), 'manage_options', 'variation-swatches-settings', array($this, 'render'), 'dashicons-editor-kitchensink', 12 );
        
    }

    public function render() {
        ?> 
        
        <!-- rander webpage -->

        <div class="variation-wrap">
            <!-- variation head -->
            <div class="variation-header-sticky">
                <div class="variation-header-wrap">
                    <div class="variation-head">
                        <div class="varitaion-logo">
                            <img src="<?php echo plugins_url( '/assets/images/wslogo-dash.png', dirname( __FILE__ ))?>" alt="">
                        </div>
                        <div class="variation-menu-outer">
                            <div class="variaion-munu-wrap">
                                <ul>
                                    <li><a href="#"><?php _e('Dashbord', 'wcvs');?></a></li>
                                    <li><a href="#"><?php _e('Addons', 'wcvs');?></a></li>
                                    <li><a href="#"><?php _e('Support', 'wcvs');?></a></li>
                                    <li><a href="#"><?php _e('Docs', 'wcvs');?></a></li>
                                    <li><a href="#"><?php _e('Activate', 'wcvs');?></a></li>
                                </ul>
                                <div class="variation-head-btns">
                                    <button class="vh-btn vh-discard-btn"><?php _e('Discard', 'wcvs');?></button>
                                    <button class="vh-btn"><?php _e('Save', 'wcvs');?></button>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
            
            
            <!-- variation head -->
            <div class="thd-theme-dashboard-wrap">
                <!-- variation body -->
                <div class="thd-wrap thd-theme-dashboard">
                    <div class="wrap">
                        <div class="thd-main">
                            <h2 style="margin: 0;padding:0;"></h2>
                            <!-- variation main content -->
                            <div class="thd-main-content">
                                
                                <div class="variation-accordion-outer">
                                    <div class="clear"></div>
                                    <!-- variation accordio -->
                                    <div class="variation-accordion-wrap">
                                        <!-- accordion item -->
                                        <div class="variation-accordion-item">
                                            <!-- accordion button -->
                                            <div class="variation-item-head var-gen-head active-accordion">
                                                <h3 class="variation-accrodion-title var-title-wi"> <?php _e('General Settings', 'wcvs');?></h3>
                                                <span class="dashicons dashicons-arrow-down-alt2"></span>
                                            </div>
                                            <!-- accordion button -->

                                            <!-- accordion content -->
                                            <div class="variation-accordion-content var-ge-accor">
                                                <div class="variation-switcher-wrap">
                                                    <!-- vairation switch item -->
                                                    <div class="variation-switcher-item">
                                                        <div class="variation-switcher-label">
                                                            <h3 class="vs-label-title"><?php _e('Auto Convert Dropdowns To Label', 'wcvs');?></h3>
                                                            <p> <?php _e('Automatically covert dropdowns to &#34;Label Swatch&#34; by default', 'wcvs');?></p>
                                                        </div>
                                                        <div class="variation-switch-field">
                                                            <label class="variation-switch">
                                                                <input type="checkbox">
                                                                <span class="slider round"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <!-- vairation switch item -->

                                                    <!-- vairation switch item -->
                                                    <div class="variation-switcher-item">
                                                        <div class="variation-switcher-label">
                                                            <h3 class="vs-label-title"> <?php _e('Auto Convert Dropdowns To Image', 'wcvs');?></h3>
                                                            <p> <?php _e('Automatically covert dropdowns to &#34;Image Swatch&#34; if variation has an image.', 'wcvs');?></p>
                                                        </div>
                                                        <div class="variation-switch-field">
                                                            <label class="variation-switch">
                                                                <input type="checkbox">
                                                                <span class="slider round"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <!-- vairation switch item -->
                                                    <!-- vairation switch item -->
                                                    <div class="variation-switcher-item">
                                                        <div class="variation-switcher-label">
                                                            <h3 class="vs-label-title"> <?php _e('Choose your swatch shape', 'wcvs');?></h3>
                                                            <p> <?php _e('select option below', 'wcvs');?></p>
                                                        </div>
                                                        <div class="variation-switch-field">
                                                            <div class="swatch-shape-wrapper">
                                                                <label class="swatch-outer-shape">
                                                                    <input type="radio" name="sahpe-1" class="swatch-shape-redio" >
                                                                    <span class="swatch-inner-shape"><span class="swatch-innter-text">S</span></span>
                                                                </label>
                                                                <label class="swatch-outer-shape">
                                                                    <input type="radio" name="sahpe-1" class="swatch-shape-redio">
                                                                    <span class="swatch-inner-shape"><span class="swatch-innter-text mid-round">S</span></span>
                                                                </label>
                                                                <label class="swatch-outer-shape">
                                                                    <input type="radio" name="sahpe-1" class="swatch-shape-redio" checked>
                                                                    <span class="swatch-inner-shape"><span class="swatch-innter-text full-round">S</span></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- vairation switch item -->
                                                    <!-- vairation switch item -->
                                                    <div class="variation-switcher-item">
                                                        <div class="variation-switcher-label">
                                                            <h3 class="vs-label-title"> <?php _e('Disable Default Plugin Stylesheet.', 'wcvs');?></h3>
                                                            <p> <?php _e('Option to enable/disable default plugin stylesheet for theme developer', 'wcvs');?></p>
                                                        </div>
                                                        <div class="variation-switch-field">
                                                            <label class="variation-switch">
                                                                <input type="checkbox">
                                                                <span class="slider round"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <!-- vairation switch item -->
                                                    <!-- vairation switch item -->
                                                    <div class="variation-switcher-item">
                                                        <div class="variation-switcher-label">
                                                            <h3 class="vs-label-title"> <?php _e('Tooltip', 'wcvs');?></h3>
                                                            <p> <?php _e('Enable or disable tooltip', 'wcvs');?></p>
                                                        </div>
                                                        <div class="variation-switch-field">
                                                            <label class="variation-switch">
                                                                <input type="checkbox">
                                                                <span class="slider round"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <!-- vairation switch item -->
                                                </div>
                                            </div>
                                            <!-- accordion content -->
                                        </div>
                                        <!-- accordion item -->

                                        <!-- accordion item -->
                                        <div class="variation-accordion-item">
                                            <!-- accordion button -->
                                            <div class="variation-item-head">
                                                <h3 class="variation-accrodion-title"><span class="dashicons dashicons-admin-appearance"></span> <?php _e('Design', 'wcvs');?></h3>
                                                <span class="dashicons dashicons-arrow-down-alt2"></span>
                                            </div>
                                            <!-- accordion button -->

                                            <!-- accordion content -->
                                            <div class="variation-accordion-content">
                                                <div class="wcvs-accor-tab-wrap">
                                                    <!-- tab btn -->
                                                    <div class="wcvs-accor-tab-btns">
                                                        <button class="accor-tab-btn active-at-btn"><?php _e('Product page', 'wcvs');?></button>
                                                        <button class="accor-tab-btn"><?php _e('Shop Archive', 'wcvs');?></button>
                                                        <button class="accor-tab-btn"><?php _e('Tooltip', 'wcvs');?></button>
                                                    </div>
                                                    <!-- tab btn -->

                                                    <!-- tab content -->
                                                    <div class="wcvs-accor-tab-content-wrap">
                                                        <!-- tab signle content -->
                                                        <div class="wcvs-accor-tab-content">
                                                            <!-- project page switcher -->
                                                            <div class="variation-switcher-wrap">
                                                                <!-- vairation switch item -->
                                                                <div class="variation-switcher-item-wrap">
                                                                    <div class="variation-switcher-item">
                                                                        <div class="variation-switcher-label">
                                                                            <h3 class="vs-label-title"><?php _e('Item styling', 'wcvs');?></h3>
                                                                            <p><?php _e('Edit the default state of your swatches', 'wcvs');?></p>
                                                                        </div>
                                                                        <div class="variation-switch-field">
                                                                            <label class="variation-switch">
                                                                                <input type="checkbox" name="item-styling">
                                                                                <span class="slider round"></span>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="swatcher-style-fields vs-item-style">
                                                                        <!-- vairation switch item -->
                                                                        <div class="variation-switcher-item">
                                                                            <div class="variation-switcher-label">
                                                                                <h3 class="vs-label-title"><?php _e('Swatch Item Color', 'wcvs');?></h3>
                                                                            </div>
                                                                            <div class="variation-switch-field">
                                                                                <input type="text" name="color" class="vs-color-picker">
                                                                            </div>
                                                                        </div>
                                                                        <!-- vairation switch item -->
                                                                        <!-- vairation switch item -->
                                                                        <div class="variation-switcher-item">
                                                                            <div class="variation-switcher-label">
                                                                                <h3 class="vs-label-title"><?php _e('Swatch Background Color', 'wcvs');?></h3>
                                                                            </div>
                                                                            <div class="variation-switch-field">
                                                                                <input type="text" name="color" class="vs-color-picker">
                                                                            </div>
                                                                        </div>
                                                                        <!-- vairation switch item -->
                                                                        <!-- vairation switch item -->
                                                                        <div class="variation-switcher-item">
                                                                            <div class="variation-switcher-label">
                                                                                <h3 class="vs-label-title"><?php _e('Swatch Border', 'wcvs');?></h3>
                                                                            </div>
                                                                            <div class="variation-switch-field">
                                                                                <div class="variation-switch-field-grid">
                                                                                    <div class="swatch-border-input">
                                                                                        <input type="number" name="border-size" class="swatch-border-type">
                                                                                        <span class="sw-input-type-text"><?php _e('px','wcvs');?></span>
                                                                                    </div>
                                                                                    <select class="br-type">
                                                                                        <option value="px"><?php _e('Solid','wcvs');?></option>
                                                                                        <option value="rem"><?php _e('dotted','wcvs');?></option>
                                                                                        <option value="pt"><?php _e('dashed','wcvs');?></option>
                                                                                    </select>
                                                                                    <input type="text" name="color" class="vs-color-picker">
                                                                                </div>
                                                                                
                                                                            </div>
                                                                        </div>
                                                                        <!-- vairation switch item -->
                                                                    </div>
                                                                </div>
                                                                
                                                                <!-- vairation switch item -->

                                                                <!-- vairation switch item -->
                                                                <div class="variation-switcher-item-wrap">
                                                                    <div class="variation-switcher-item">
                                                                        <div class="variation-switcher-label">
                                                                            <h3 class="vs-label-title"><?php _e('Item hover styling', 'wcvs');?></h3>
                                                                            <p><?php _e('Edit the hover state of your swatches', 'wcvs');?></p>
                                                                        </div>
                                                                        <div class="variation-switch-field">
                                                                            <label class="variation-switch">
                                                                                <input type="checkbox" name="item-hover">
                                                                                <span class="slider round"></span>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="swatcher-style-fields vs-item-hover">
                                                                        <!-- vairation switch item -->
                                                                        <div class="variation-switcher-item">
                                                                            <div class="variation-switcher-label">
                                                                                <h3 class="vs-label-title"><?php _e('Swatch hover Color', 'wcvs');?></h3>
                                                                            </div>
                                                                            <div class="variation-switch-field">
                                                                                <input type="text" name="color" class="vs-color-picker">
                                                                            </div>
                                                                        </div>
                                                                        <!-- vairation switch item -->
                                                                        <!-- vairation switch item -->
                                                                        <div class="variation-switcher-item">
                                                                            <div class="variation-switcher-label">
                                                                                <h3 class="vs-label-title"><?php _e('Swatch Background Color', 'wcvs');?></h3>
                                                                            </div>
                                                                            <div class="variation-switch-field">
                                                                                <input type="text" name="color" class="vs-color-picker">
                                                                            </div>
                                                                        </div>
                                                                        <!-- vairation switch item -->
                                                                        <!-- vairation switch item -->
                                                                        <div class="variation-switcher-item">
                                                                            <div class="variation-switcher-label">
                                                                                <h3 class="vs-label-title"><?php _e('Swatch Border', 'wcvs');?></h3>
                                                                            </div>
                                                                            <div class="variation-switch-field">
                                                                                <div class="variation-switch-field-grid">
                                                                                    <div class="swatch-border-input">
                                                                                        <input type="number" name="border-size" class="swatch-border-type">
                                                                                        <span class="sw-input-type-text"><?php _e('px','wcvs');?></span>
                                                                                    </div>
                                                                                    <select class="br-type">
                                                                                        <option value="px"><?php _e('Solid','wcvs');?></option>
                                                                                        <option value="rem"><?php _e('dotted','wcvs');?></option>
                                                                                        <option value="pt"><?php _e('dashed','wcvs');?></option>
                                                                                    </select>
                                                                                    <input type="text" name="color" class="vs-color-picker">
                                                                                </div>
                                                                                
                                                                            </div>
                                                                        </div>
                                                                        <!-- vairation switch item -->
                                                                    </div>
                                                                </div>
                                                                
                                                                <!-- vairation switch item -->
                                                                <!-- vairation switch item -->
                                                                <div class="variation-switcher-item-wrap">
                                                                    <div class="variation-switcher-item">
                                                                        <div class="variation-switcher-label">
                                                                            <h3 class="vs-label-title"><?php _e('Item Selected Styling', 'wcvs');?></h3>
                                                                            <p><?php _e('Edit the selected state of your swatches', 'wcvs');?></p>
                                                                        </div>
                                                                        <div class="variation-switch-field">
                                                                            <label class="variation-switch">
                                                                                <input type="checkbox" name="item-selected">
                                                                                <span class="slider round"></span>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="swatcher-style-fields vs-item-selected">
                                                                        <!-- vairation switch item -->
                                                                        <div class="variation-switcher-item">
                                                                            <div class="variation-switcher-label">
                                                                                <h3 class="vs-label-title"><?php _e('Swatch selected Color', 'wcvs');?></h3>
                                                                            </div>
                                                                            <div class="variation-switch-field">
                                                                                <input type="text" name="color" class="vs-color-picker">
                                                                            </div>
                                                                        </div>
                                                                        <!-- vairation switch item -->
                                                                        <!-- vairation switch item -->
                                                                        <div class="variation-switcher-item">
                                                                            <div class="variation-switcher-label">
                                                                                <h3 class="vs-label-title"><?php _e('Swatch Selected Background Color', 'wcvs');?></h3>
                                                                            </div>
                                                                            <div class="variation-switch-field">
                                                                                <input type="text" name="color" class="vs-color-picker">
                                                                            </div>
                                                                        </div>
                                                                        <!-- vairation switch item -->
                                                                        <!-- vairation switch item -->
                                                                        <div class="variation-switcher-item">
                                                                            <div class="variation-switcher-label">
                                                                                <h3 class="vs-label-title"><?php _e('Swatch Border', 'wcvs');?></h3>
                                                                            </div>
                                                                            <div class="variation-switch-field">
                                                                                <div class="variation-switch-field-grid">
                                                                                    <div class="swatch-border-input">
                                                                                        <input type="number" name="border-size" class="swatch-border-type">
                                                                                        <span class="sw-input-type-text"><?php _e('px','wcvs');?></span>
                                                                                    </div>
                                                                                    <select class="br-type">
                                                                                        <option value="px"><?php _e('Solid','wcvs');?></option>
                                                                                        <option value="rem"><?php _e('dotted','wcvs');?></option>
                                                                                        <option value="pt"><?php _e('dashed','wcvs');?></option>
                                                                                    </select>
                                                                                    <input type="text" name="color" class="vs-color-picker">
                                                                                </div>
                                                                                
                                                                            </div>
                                                                        </div>
                                                                        <!-- vairation switch item -->
                                                                    </div>
                                                                </div>
                                                                
                                                                <!-- vairation switch item -->
                                                               
                                                                <!-- vairation switch item -->
                                                                <div class="variation-switcher-item-wrap">
                                                                    <div class="variation-switcher-item">
                                                                        <div class="variation-switcher-label">
                                                                            <h3 class="vs-label-title"><?php _e('Swatch Text font size', 'wcvs');?></h3>
                                                                            <p><?php _e('The default font size that will be used for your swatches text', 'wcvs');?></p>
                                                                        </div>
                                                                        <div class="variation-switch-field">
                                                                            <label class="variation-switch">
                                                                                <input type="checkbox" name="item-font">
                                                                                <span class="slider round"></span>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="swatcher-style-fields vs-item-font">
                                                                        <!-- vairation switch item -->
                                                                        <div class="variation-switcher-item">
                                                                            <div class="variation-switcher-label">
                                                                                <h3 class="vs-label-title"><?php _e('Text font size', 'wcvs');?></h3>
                                                                            </div>
                                                                            <div class="variation-switch-field">
                                                                                <div class="variation-switch-field-grid">
                                                                                    <input type="number" name="text-font-size" class="font-size-input" placeholder="All">
                                                                                    <select name="item-font-type">
                                                                                        <option value="px"><?php _e('px','wcvs');?></option>
                                                                                        <option value="rem"><?php _e('rem','wcvs');?></option>
                                                                                        <option value="pt"><?php _e('pt','wcvs');?></option>
                                                                                    </select>
                                                                                </div>
                                                                                
                                                                            </div>
                                                                        </div>
                                                                        <!-- vairation switch item -->
                                                                       
                                                                    </div>
                                                                </div>
                                                                
                                                                <!-- vairation switch item -->

                                                                <!-- vairation switch item -->
                                                                <div class="variation-switcher-item vs-quote-item">
                                                                    <div class="variation-switcher-label">
                                                                        <h3 class="vs-label-title"><?php _e('Margin and Padding', 'wcvs');?></h3>
                                                                        <p><?php _e('Swatch Item -> represents each individual swatch item.', 'wcvs');?></p>
                                                                        <p><?php _e('Swatches Wrapper -> represents the container for the group of swatches.', 'wcvs');?></p>
                                                                    </div>
                                                                </div>
                                                                <!-- vairation switch item -->

                                                                <!-- vairation switch item -->
                                                                <div class="variation-switcher-item">
                                                                    <div class="variation-switcher-label">
                                                                        <h3 class="vs-label-title"><?php _e('Swatch Item Margin', 'wcvs');?></h3>
                                                                    </div>
                                                                    <div class="variation-switch-field variation-switch-multi-field ">
                                                                        <input type="number" name="mar-top" placeholder="top">
                                                                        <input type="number" name="mar-right" placeholder="right">
                                                                        <input type="number" name="mar-bottom" placeholder="bottom">
                                                                        <input type="number" name="mar-left" placeholder="left">
                                                                        <select name="mar-type">
                                                                            <option value="px"><?php _e('px', 'wcvs');?></option>
                                                                            <option value="rem"><?php _e('rem', 'wcvs');?></option>
                                                                            <option value="pt"><?php _e('pt', 'wcvs');?></option>
                                                                            <option value="em"><?php _e('em', 'wcvs');?></option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <!-- vairation switch item -->

                                                                <!-- vairation switch item -->
                                                                <div class="variation-switcher-item">
                                                                    <div class="variation-switcher-label">
                                                                        <h3 class="vs-label-title"><?php _e('Swatch Item Padding', 'wcvs');?></h3>
                                                                    </div>
                                                                    <div class="variation-switch-field variation-switch-multi-field ">
                                                                        <input type="number" name="pad-top" placeholder="top">
                                                                        <input type="number" name="pad-right" placeholder="right">
                                                                        <input type="number" name="pad-bottom" placeholder="bottom">
                                                                        <input type="number" name="pad-left" placeholder="left">
                                                                        <select name="pad-type">
                                                                            <option value="px"><?php _e('px', 'wcvs');?></option>
                                                                            <option value="rem"><?php _e('rem', 'wcvs');?></option>
                                                                            <option value="pt"><?php _e('pt', 'wcvs');?></option>
                                                                            <option value="em"><?php _e('em', 'wcvs');?></option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <!-- vairation switch item -->
                                                                <!-- vairation switch item -->
                                                                <div class="variation-switcher-item">
                                                                    <div class="variation-switcher-label">
                                                                        <h3 class="vs-label-title"><?php _e('Swatch Wrapper Margin', 'wcvs');?></h3>
                                                                    </div>
                                                                    <div class="variation-switch-field variation-switch-multi-field ">
                                                                        <input type="number" name="wrm-top" placeholder="top">
                                                                        <input type="number" name="wrm-right" placeholder="right">
                                                                        <input type="number" name="wrm-bottom" placeholder="bottom">
                                                                        <input type="number" name="wrm-left" placeholder="left">
                                                                        <select name="wrm-type">
                                                                            <option value="px"><?php _e('px', 'wcvs');?></option>
                                                                            <option value="rem"><?php _e('rem', 'wcvs');?></option>
                                                                            <option value="pt"><?php _e('pt', 'wcvs');?></option>
                                                                            <option value="em"><?php _e('em', 'wcvs');?></option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <!-- vairation switch item -->
                                                                <!-- vairation switch item -->
                                                                <div class="variation-switcher-item">
                                                                    <div class="variation-switcher-label">
                                                                        <h3 class="vs-label-title"><?php _e('Swatch Wrapper Padding', 'wcvs');?></h3>
                                                                    </div>
                                                                    <div class="variation-switch-field variation-switch-multi-field ">
                                                                        <input type="number" name="wrp-top" placeholder="top">
                                                                        <input type="number" name="wrp-right" placeholder="right">
                                                                        <input type="number" name="wrp-bottom" placeholder="bottom">
                                                                        <input type="number" name="wrp-left" placeholder="left">
                                                                        <select name="wrp-type">
                                                                            <option value="px"><?php _e('px', 'wcvs');?></option>
                                                                            <option value="rem"><?php _e('rem', 'wcvs');?></option>
                                                                            <option value="pt"><?php _e('pt', 'wcvs');?></option>
                                                                            <option value="em"><?php _e('em', 'wcvs');?></option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <!-- vairation switch item -->
                                                            </div>
                                                            <!-- project page switcher -->
                                                        </div>
                                                        <!-- tab signle content -->
                                                        <!-- tab signle content -->
                                                        <div class="wcvs-accor-tab-content">
                                                            <h2><?php _e('Shop archive', 'wcvs');?></h2>
                                                        </div>
                                                        <!-- tab signle content -->
                                                        <!-- tab signle content -->
                                                        <div class="wcvs-accor-tab-content">
                                                            <h2><?php _e('Tooltip', 'wcvs');?></h2>
                                                        </div>
                                                        <!-- tab signle content -->
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- accordion content -->
                                        </div>
                                        <!-- accordion item -->

                                        <!-- accordion item -->
                                        <div class="variation-accordion-item">
                                            <!-- accordion button -->
                                            <div class="variation-item-head">
                                                <h3 class="variation-accrodion-title"><span class="dashicons dashicons-admin-generic"></span>  <?php _e('Archive / Shop', 'wcvs');?></h3>
                                                <span class="dashicons dashicons-arrow-down-alt2"></span>
                                            </div>
                                            <!-- accordion button -->

                                            <!-- accordion content -->
                                            <div class="variation-accordion-content">
                                                <div class="variation-switcher-wrap">
                                                    <!-- vairation switch item -->
                                                    <div class="variation-switcher-item">
                                                        <div class="variation-switcher-label">
                                                            <h3 class="vs-label-title"><?php _e('Show Swatches Label', 'wcvs');?></h3>
                                                            <p> <?php _e('This will show your swatches when users are browsing your main store page', 'wcvs');?></p>
                                                        </div>
                                                        <div class="variation-switch-field">
                                                            <label class="variation-switch">
                                                                <input type="checkbox">
                                                                <span class="slider round"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <!-- vairation switch item -->

                                                    <!-- vairation switch item -->
                                                    <div class="variation-switcher-item">
                                                        <div class="variation-switcher-label">
                                                            <h3 class="vs-label-title"> <?php _e('Show clear link', 'wcvs');?></h3>
                                                            <p> <?php _e('This allows users to clean section', 'wcvs');?></p>
                                                        </div>
                                                        <div class="variation-switch-field">
                                                            <label class="variation-switch">
                                                                <input type="checkbox">
                                                                <span class="slider round"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <!-- vairation switch item -->
                                                    <!-- vairation switch item -->
                                                    <div class="variation-switcher-item">
                                                        <div class="variation-switcher-label">
                                                            <h3 class="vs-label-title"> <?php _e('Swatch alignment', 'wcvs');?></h3>
                                                            <p> <?php _e('Chose how to swatches are displayed', 'wcvs');?></p>
                                                        </div>
                                                        <div class="variation-switch-field">
                                                            <select class="switch-alignment">
                                                                <option value="left"> <?php _e('Left', 'wcvs');?></option>
                                                                <option value="center"> <?php _e('Center', 'wcvs');?></option>
                                                                <option value="right"> <?php _e('right', 'wcvs');?></option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <!-- vairation switch item -->
                                                    <!-- vairation switch item -->
                                                    <div class="variation-switcher-item">
                                                        <div class="variation-switcher-label">
                                                            <h3 class="vs-label-title"> <?php _e('Swatches position', 'wcvs');?></h3>
                                                            <p> <?php _e('Choose where to insert swatches', 'wcvs');?></p>
                                                        </div>
                                                        <div class="variation-switch-field">
                                                            <select class="switch-alignment">
                                                                <option value="befor-price"> <?php _e('Before price', 'wcvs');?></option>
                                                                <option value="after-price"> <?php _e('After price', 'wcvs');?></option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <!-- vairation switch item -->
                                                    <!-- vairation switch item -->
                                                    <div class="variation-switcher-item">
                                                        <div class="variation-switcher-label">
                                                            <h3 class="vs-label-title"> <?php _e('Enable Tooltip', 'wcvs');?></h3>
                                                            <p> <?php _e('Enhance the shopping experience for your users', 'wcvs');?></p>
                                                        </div>
                                                        <div class="variation-switch-field">
                                                            <label class="variation-switch">
                                                                <input type="checkbox">
                                                                <span class="slider round"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <!-- vairation switch item -->

                                                    <!-- vairation switch item -->
                                                    <div class="variation-switcher-item">
                                                        <div class="variation-switcher-label">
                                                            <h3 class="vs-label-title"> <?php _e('Swatch limit', 'wcvs');?></h3>
                                                            <p><?php _e('Set a max amount of swatches to show', 'wcvs');?></p>
                                                        </div>
                                                        <div class="variation-switch-field">
                                                            <input type="number" class="swatch-limit-field">
                                                        </div>
                                                    </div>
                                                    <!-- vairation switch item -->
                                                </div>
                                            </div>
                                            <!-- accordion content -->
                                        </div>
                                        <!-- accordion item -->
                                    </div>
                                    <!-- variation accordio -->
                                </div>
                                <div class="clear"></div>
                                
                            </div>
                            <!-- variation main content -->

                            <!-- variation sidebar -->
                            <div class="thd-main-sidebar">
                                <div class="swatch-var-widget-wrap">
                                    <!-- widget -->
                                
                                    <div class="swatch-var-widget">
                                        <h3 class="swatch-video-title"> <?php _e('Getting Started','wcvs')?></h3>
                                        <div class="swat-video-frame">
                                            <iframe src="https://www.youtube.com/embed/6stlCkUDG_s" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                        </div>
                                    </div>
                                    <!-- widget -->

                                    <!-- widget -->
                                    <div class="swatch-var-widget">
                                        <h2 class="swatch-video-title"> <?php _e('Works well with..','wcvs')?></h3>
                                        <div class="swatch-var-addon-wrap">
                                            <div class="swatch-var-addon">
                                                <a href="#" class="swatch-addon-link"><h2><?php _e('WooCommerce Show single variation','wcvs')?></h2></a>
                                                <p><?php _e('Display individual products variations in your product','wcvs')?></p>
                                                <a href="#" class="swatch-submit-link"><?php _e('Get It now','wcvs')?></a>
                                            </div>
                                            <div class="swatch-var-addon">
                                                <a href="#" class="swatch-addon-link"><h2><?php _e('WooCommerce Variations Gallery','wcvs')?></h2></a>
                                                <p><?php _e('Display individual products variations in your product','wcvs')?></p>
                                                <a href="#" class="swatch-submit-link"><?php _e('Get It now','wcvs')?></a>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- widget -->
                                </div>
                            </div>
                            <!-- variation sidebar -->
                        </div>
                        <div class="clear"></div>

                        <!-- variation footer -->
                        <div class="swatch-variation-footer">
                            <div class="thd-panel thd-panel-support">
                                <div class="thd-panel-head">
                                    <h3 class="thd-panel-title"> <?php _e('Support','wcvs');?></h3>
                                </div>
                                <div class="thd-panel-content">
                                    <div class="thd-conttent-primary">
                                        <div class="thd-title">
                                            <?php _e('Need help? Were here for you!','wcvs');?>								</div>

                                        <div class="thd-description"><?php _e('Have a question? Hit a bug? Get the help you need, when you need it from our friendly support staff.','wcvs');?></div>

                                        <div class="thd-button-wrap">
                                            <a href="https://forums.athemes.com/" class="thd-button button" target="_blank"><?php _e('Get Support	','wcvs');?></a>
                                        </div>
                                    </div>

                                    <div class="thd-conttent-secondary">
                                        <div class="thd-title">
                                            <?php _e('Priority Support','wcvs');?>										<div class="thd-badge"><?php _e('pro','wcvs');?></div>
                                        </div>

                                        <div class="thd-description"><?php _e('Want your questions answered faster? Go Pro to be first in the queue!','wcvs');?></div>

                                        <div class="thd-button-wrap">
                                            <a href="https://athemes.com/theme/sydney-pro/?utm_source=theme_info&amp;utm_medium=link&amp;utm_campaign=Sydney" class="thd-button button" target="_blank"><?php _e('Go PRO','wcvs');?></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="thd-panel thd-panel-community">
                                <div class="thd-panel-head">
                                    <h3 class="thd-panel-title"><?php _e('Custom development','wcvs');?></h3>
                                </div>
                                <div class="thd-panel-content">
                                    <div class="thd-title">
                                    <?php _e('Do you need a custom plugin or edit to your site?','wcvs');?></div>

                                    <div class="thd-description"><?php _e('We have created top-class themes and  products for buddyPress products or create something cutsom.', 'wcvs');?></div>

                                    <div class="thd-button-wrap">
                                        <a href="https://community.athemes.com/" class="thd-button button" target="_blank"><?php _e('Start a Project');?></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- variation footer -->

                        <!--video tutorail  -->
                        <div class="swatch-variation-tutorail">
                            <div class="theplus-panel-row theplus-mt-50">
                                <div class="theplus-panel-col theplus-panel-col-100">
                                    <div class="theplus-panel-sec theplus-p-20 theplus-welcome-video">
                                        <div class="theplus-sec-title">Video Tutorials</div>
                                        <div class="theplus-sec-subtitle">Checkout Few of our latest video tutorials</div>
                                        <div class="theplus-sec-border"></div>
                                        <div class="theplus-panel-row theplus-panel-relative">
                                            <a href="https://www.youtube.com/playlist?list=PLFRO-irWzXaLK9H5opSt88xueTnRhqvO5 " class="theplus-more-video" target="_blank">Our Full Playlist</a>
                                            <div class="theplus-panel-col theplus-panel-col-25">
                                                <a href="https://youtu.be/HY5KlYuWP5k" class="theplus-panel-video-list" target="_blank"><img src="<?php echo get_template_directory_uri(  );?>/inc/admin/assets/images/video-1.jpg"></a>
                                            </div>
                                            <div class="theplus-panel-col theplus-panel-col-25">
                                                <a href="https://youtu.be/9-8Ftlb79tI" class="theplus-panel-video-list" target="_blank"><img src="<?php echo get_template_directory_uri(  );?>/inc/admin/assets/images/video-2.jpg"></a>
                                            </div>
                                            <div class="theplus-panel-col theplus-panel-col-25">
                                                <a href="https://youtu.be/Bwp3GBOlkaw" class="theplus-panel-video-list" target="_blank"><img src="<?php echo get_template_directory_uri(  );?>/inc/admin/assets/images/video-3.jpg"></a>
                                            </div>
                                            <div class="theplus-panel-col theplus-panel-col-25">
                                                <a href="https://youtu.be/kl2xSnl2YqM" class="theplus-panel-video-list" target="_blank"><img src="<?php echo get_template_directory_uri(  );?>/inc/admin/assets/images/video-4.jpg"></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--video tutorail  -->
                        
                    </div>
                </div>
                <!-- variation body -->
            </div>
        </div>

        <?php
    }
}


?>