<?php
$wp_environment = usces_get_wp_environment();
$environment = $wp_environment['environment'];
$theme = $wp_environment['theme'];
$active_plugins = $wp_environment['active_plugins'];
$inactive_plugins = $wp_environment['inactive_plugins'];
$welcart_information = get_welcart_system_information();
$loaded_extensions = get_loaded_extensions();
$ini_confs = ini_get_all();
?>

<div id="download_env_info">
    <button id="btn_download_env_info" ><?php echo esc_html_e( 'Download environment information', 'usces' ); ?></button>
</div>
<table class="wc_status_table widefat" cellspacing="0" id="wp_environment_status">
    <thead>
    <tr>
        <th colspan="3" data-export-label="WordPress Settings"><h2><?php esc_html_e( 'WordPress Settings', 'usces' ); ?></h2></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td data-export-label="WordPress Address (URL)"><?php esc_html_e( 'WordPress Address (URL)', 'usces' ); ?>:</td>

        <td><?php echo esc_html( $environment['site_url'] ); ?></td>
    </tr>
    <tr>
        <td data-export-label="Site Address (URL)"><?php esc_html_e( 'Site Address (URL)', 'usces' ); ?>:</td>

        <td><?php echo esc_html( $environment['home_url'] ); ?></td>
    </tr>
    <tr>
        <td data-export-label="WordPress Version"><?php esc_html_e( 'WordPress Version', 'usces' ); ?>:</td>
        <td>
            <?php
            $version_check = wp_remote_get( 'https://api.wordpress.org/core/version-check/1.7/' );
            $api_response  = json_decode( wp_remote_retrieve_body( $version_check ), true );

            if ( $api_response && isset( $api_response['offers'], $api_response['offers'][0], $api_response['offers'][0]['version'] ) ) {
                $latest_version = $api_response['offers'][0]['version'];
            } else {
                $latest_version = $environment['wp_version'];
            }
            if ( version_compare( $environment['wp_version'], $latest_version, '<' ) ) {
                /* Translators: %1$s: Current version, %2$s: New version */
                echo  esc_html( $environment['wp_version'] ).' ' . sprintf( esc_html__( '%1$s - There is a newer version of WordPress available (%2$s)', 'woocommerce' ), esc_html( $environment['wp_version'] ), esc_html( $latest_version ) ) . '</mark>';
            } else {
                echo esc_html( $environment['wp_version'] );
            }
            ?>
        </td>
    </tr>
    <tr>
        <td data-export-label="Site Language"><?php esc_html_e( 'Site Language', 'usces' ); ?>:</td>

        <td><?php echo esc_html( $environment['site_language'] ); ?></td>
    </tr>
    <tr>
        <td data-export-label="Timezone"><?php esc_html_e( 'Timezone', 'usces' ); ?>:</td>

        <td><?php echo esc_html( $environment['timezone'] ); ?></td>
    </tr>
    <tr>
        <td data-export-label="Number of media files"><?php esc_html_e( 'Number of media files', 'usces' ); ?>:</td>

        <td><?php echo esc_html( $environment['number_of_media'] ); ?></td>
    </tr>
    <tr>
        <td data-export-label="Blog pages show at most"><?php esc_html_e( 'Blog pages show at most', 'usces' ); ?>:</td>

        <td><?php echo esc_html( $environment['number_of_post'] ); ?></td>
    </tr>
    <tr>
        <td data-export-label="Search engine visibility"><?php esc_html_e( 'Search engine visibility', 'usces' ); ?>:</td>

        <td> <?php echo ($environment['blog_public']) ? '&nbsp;' : esc_html_e("Discourage search engines from indexing this site", 'usces')?></td>
    </tr>
    <tr>
        <td data-export-label="Thumbnail size"><?php esc_html_e( 'Thumbnail size', 'usces' ); ?>:</td>

        <td><?php esc_html_e( 'Width', 'usces' ); ?> <?php echo esc_html( $environment['thumbnail_size']['width'] ); ?> <?php esc_html_e( 'Height', 'usces' ); ?> <?php echo esc_html( $environment['thumbnail_size']['height'] ); ?></td>
    </tr>
    <tr>
        <td data-export-label="Image medium size"><?php esc_html_e( 'Image medium size', 'usces' ); ?>:</td>

        <td><?php esc_html_e( 'Width', 'usces' ); ?> <?php echo esc_html( $environment['medium_size']['width'] ); ?> <?php esc_html_e( 'Height', 'usces' ); ?> <?php echo esc_html( $environment['medium_size']['height'] ); ?></td>
    </tr>

    <tr>
        <td data-export-label="Image large size"><?php esc_html_e( 'Image large size', 'usces' ); ?>:</td>

        <td><?php esc_html_e( 'Width', 'usces' ); ?> <?php echo esc_html( $environment['large_size']['width'] ); ?> <?php esc_html_e( 'Height', 'usces' ); ?> <?php echo esc_html( $environment['large_size']['height'] ); ?></td>
    </tr>

    <tr>
        <td data-export-label="Permalink Settings"><?php esc_html_e( 'Permalink Settings', 'usces' ); ?>:</td>

        <td><?php echo esc_html( $environment['permalinks'] ); ?></td>
    </tr>
    <tr>
        <td data-export-label="WP Multisite"><?php esc_html_e( 'WordPress Multisite', 'usces' ); ?>:</td>
        <td><?php echo ( $environment['wp_multisite'] ) ? esc_html_e("ON", 'usces') : '&nbsp;'; ?></td>
    </tr>
    <tr>
        <td data-export-label="WP Memory Limit"><?php esc_html_e( 'WordPress Memory Limit', 'usces' ); ?>:</td>
        <td><?php echo  esc_html( size_format( $environment['wp_memory_limit'] ) );?></td>
    </tr>
    <tr>
        <td data-export-label="WP Debug Mode"><?php esc_html_e( 'WordPress Debug Mode', 'usces' ); ?>:</td>
        <td><?php echo ( $environment['wp_debug_mode'] ) ? esc_html_e("ON", 'usces') : esc_html_e("OFF", 'usces'); ?></td>
    </tr>
    </tbody>
</table>
<table class="wc_status_table widefat" cellspacing="0" id="theme_status">
    <thead>
    <tr>
        <th colspan="3" data-export-label="Theme"><h2><?php esc_html_e( 'Theme', 'usces' ); ?></h2></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td data-export-label="Theme name"><?php esc_html_e( 'Theme name', 'usces' ); ?>:</td>

        <td><?php echo esc_html( $theme['name'] ); ?></td>
    </tr>
    <tr>
        <td data-export-label="Theme version"><?php esc_html_e( 'Theme version', 'usces' ); ?>:</td>

        <td><?php echo esc_html( $theme['version'] ); ?></td>
    </tr>
    <tr>
        <td data-export-label="Author URL"><?php esc_html_e( 'Author URL', 'usces' ); ?>:</td>

        <td><?php echo esc_html( $theme['author_url'] ); ?></td>
    </tr>
    <?php if(isset($theme['is_child_theme']) && $theme['is_child_theme']){ ?>
        <tr>
            <td data-export-label="Parent theme name"><?php esc_html_e( 'Parent theme name', 'usces' ); ?>:</td>

            <td><?php echo esc_html( $theme['parent_name'] ); ?></td>
        </tr>
        <tr>
            <td data-export-label="Parent theme version"><?php esc_html_e( 'Parent theme version', 'usces' ); ?>:</td>

            <td><?php echo esc_html( $theme['parent_version'] ); ?></td>
        </tr>
        <tr>
            <td data-export-label="Parent author URL"><?php esc_html_e( 'Parent author URL', 'usces' ); ?>:</td>

            <td><?php echo esc_html( $theme['parent_author_url'] ); ?></td>
        </tr>

    <?php } ?>
    </tbody>
</table>
<table class="wc_status_table widefat" cellspacing="0" id="active_plugin_status">
    <thead>
    <tr>
        <th colspan="3" data-export-label="Active Plugins"><h2><?php esc_html_e( 'Active Plugins', 'usces' ); ?></h2></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($active_plugins as $plugin){ ?>
        <tr>
            <td><a href="<?php echo esc_attr($plugin['url']); ?>"><?php echo esc_html( $plugin['name'] ); ?></a></td>

            <td>
                <?php echo esc_html_e('by', 'usces') . ' '. esc_html($plugin['author_name']) . ' - ' . esc_html($plugin['version']); ?>
                <?php echo ($plugin['version'] != $plugin['version_latest']) ? '('.esc_html__('update to version', 'usces') . ' ' . esc_html($plugin['version_latest']). ' ' . esc_html__('is available', 'usces') . ')' : ""; ?>
            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>
<table class="wc_status_table widefat" cellspacing="0" id="inactive_plugin_status">
    <thead>
    <tr>
        <th colspan="3" data-export-label="Inactive Plugins"><h2><?php esc_html_e( 'Inactive Plugins', 'usces' ); ?></h2></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($inactive_plugins as $plugin){ ?>
        <tr>
            <td><a href="<?php echo esc_attr($plugin['url']); ?>"><?php echo esc_html( $plugin['name'] ); ?></a></td>
            <td>
                <?php echo esc_html_e('by', 'usces') . ' '. esc_html($plugin['author_name']) . ' - ' . esc_html($plugin['version']); ?>
                <?php echo ($plugin['version'] != $plugin['version_latest']) ? '('.esc_html__('update to version', 'usces') . ' ' . esc_html($plugin['version_latest']). ' ' . esc_html__('is available', 'usces') . ')' : ""; ?>
            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>


<table class="wc_status_table widefat" cellspacing="0" id="Welcart_status">
    <thead>
    <tr>
        <th colspan="3" data-export-label="General Setting"><h2><?php esc_html_e( 'General Setting', 'usces' ); ?></h2></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td data-export-label="Welcart Version"><?php esc_html_e( 'Welcart Version', 'usces' ); ?>:</td>

        <td><?php echo esc_html( $welcart_information['version'] ); ?></td>
    </tr>
    <tr>
        <td data-export-label="number of item"><?php esc_html_e( 'number of item', 'usces' ); ?>:</td>

        <td><?php echo esc_html( $welcart_information['total_items'] ); ?></td>
    </tr>
    <tr>
        <td data-export-label="SKU total number"><?php esc_html_e( 'SKU total number', 'usces' ); ?>:</td>

        <td><?php echo esc_html( $welcart_information['total_sku'] ); ?></td>
    </tr>
    <tr>
        <td data-export-label="Display mode"><?php esc_html_e( 'Display mode', 'usces' ); ?>:</td>
        <td><?php echo ($welcart_information['maintenance_mode']) ? esc_html_e('Under Maintenance','usces'  ) : esc_html_e('Normal business','usces'  ); ?></td>
    </tr>
    <tr>
        <td data-export-label="Campaign Schedule"><?php esc_html_e( 'Campaign Schedule', 'usces' ); ?>:</td>
        <td>
            <p><?php echo esc_html__('starting time','usces'); ?>:
                <?php foreach ($welcart_information['campaign_schedule']['start'] as $key => $value){ ?>
                    <span> <?php echo esc_html($value); ?> <?php echo esc_html_e($key ,'usces'); ?> </span>
                <?php } ?>
            </p>
            <p><?php echo esc_html__('date and time of termination','usces'); ?>:
                <?php foreach ($welcart_information['campaign_schedule']['end'] as $key => $value){ ?>
                    <span> <?php echo esc_html($value); ?> <?php echo esc_html_e($key ,'usces'); ?> </span>
                <?php } ?>
            </p>
        </td>
    </tr>
    <tr>
        <td data-export-label="E-mail address for ordering"><?php esc_html_e( 'E-mail address for ordering', 'usces' ); ?>:</td>
        <td><?php echo esc_html( $welcart_information['order_mail'] ); ?></td>
    </tr>
    <tr>
        <td data-export-label="Sender's e-mail address"><?php esc_html_e( 'Sender\'s e-mail address', 'usces' ); ?>:</td>
        <td><?php echo esc_html( $welcart_information['sender_mail'] ); ?></td>
    </tr>
    <tr>
        <td data-export-label="Conditions for free shipping"><?php esc_html_e( 'Conditions for free shipping', 'usces' ); ?>:</td>
        <td><?php echo ( ! empty( $welcart_information['postage_privilege'] ) ) ? esc_html( $welcart_information['postage_privilege'] ) . esc_html( 'Above', 'usces' ) : '&nbsp;'; ?></td>
    </tr>
    <tr>
        <td data-export-label="default limitation number of purchase"><?php esc_html_e( 'default limitation number of purchase', 'usces' ); ?>:</td>
        <td><?php echo ( ! empty( $welcart_information['purchase_limit'] ) ) ? esc_html( $welcart_information['purchase_limit'] ) . esc_html( 'maximum amount', 'usces' ) : '&nbsp;'; ?></td>
    </tr>
    <tr>
        <td data-export-label="initial value of date of sending out."><?php esc_html_e( 'initial value of date of sending out.', 'usces' ); ?>:</td>
        <td><?php echo (isset($welcart_information['shipping_rule_text'][$welcart_information['shipping_rule']])) ? esc_html($welcart_information['shipping_rule_text'][$welcart_information['shipping_rule']]) : '&nbsp;'; ?></td>
    </tr>
    <tr>
        <td data-export-label="Tax treatment"><?php esc_html_e( 'Tax treatment', 'usces' ); ?>:</td>
        <td><?php echo ($welcart_information['tax_mode'] == 'include') ? esc_html_e("Included", 'usces' ) : esc_html_e("Excluded", 'usces' ); ?></td>
    </tr>
    <tr>
        <td data-export-label="Tax target"><?php esc_html_e( 'Tax target', 'usces' ); ?>:</td>
        <td><?php echo ($welcart_information['tax_target'] == 'products') ?  esc_html_e("Only Products", 'usces' ) : esc_html_e("All Amount", 'usces' ); ?></td>
    </tr>
    <tr>
        <td data-export-label="Applicable tax rate"><?php esc_html_e( 'Applicable tax rate', 'usces' ); ?>:</td>
        <td><?php echo ($welcart_information['applicable_taxrate'] == 'reduced') ? esc_html_e("Reduced tax rate", 'usces' ) : esc_html_e("Standard tax rate", 'usces' ); ?></td>
    </tr>

    <tr>
        <td data-export-label="Percentage of Consumption tax"><?php esc_html_e( 'Percentage of Consumption tax', 'usces' ); ?>:</td>
        <td><?php echo esc_html( ucfirst($welcart_information['tax_rate']) ); ?> %</td>
    </tr>
    <!--    <tr>-->
    <!--        <td data-export-label="Applicable tax rate">--><?php //esc_html_e( 'Applicable tax rate', 'usces' ); ?><!--:</td>-->
    <!--        <td>--><?php //echo ($welcart_information['applicable_taxrate'] == 'reduced') ? esc_html_e('Yes','usces'  ) : esc_html_e('No','usces'  ); ?><!--</td>-->
    <!--    </tr>-->
    <tr>
        <td data-export-label="Reduced tax rate"><?php esc_html_e( 'Reduced tax rate', 'usces' ); ?>:</td>
        <td><?php echo esc_html_e($welcart_information['reduced_tax_rate']); ?> %</td>
    </tr>

    <tr>
        <td data-export-label="method of Calculation of the tax"><?php esc_html_e( 'method of Calculation of the tax', 'usces' ); ?>:</td>
        <td><?php switch($welcart_information['tax_method']){
                case 'cutting':
                    echo esc_html__('drop fractions','usces');
                    break;
                case 'bring':
                    echo esc_html__('raise to a unit','usces');
                    break;
                case 'rounding':
                    echo esc_html__('round up numbers of five and above and round down anything under','usces');
                    break;
                default:
                    echo "";
                    break;
            } ?></td>
    </tr>
    <tr>
        <td data-export-label="membership syetem"><?php esc_html_e( 'membership syetem', 'usces' ); ?>:</td>
        <td><?php echo ($welcart_information['membership_system'] == 'activate') ? esc_html_e('to use', 'usces') : esc_html_e('not to use', 'usces'); ?></td>
    </tr>
    <tr>
        <td data-export-label="membership points"><?php esc_html_e( 'membership points', 'usces' ); ?>:</td>
        <td><?php echo ($welcart_information['membership_point'] == 'activate') ? esc_html_e('to grant', 'usces') : esc_html_e('not to grant', 'usces'); ?></td>
    </tr>

    <tr>
        <td data-export-label="Areas of Point Redemption"><?php esc_html_e( 'Areas of Point Redemption', 'usces' ); ?>:</td>
        <td><?php echo ($welcart_information['point_coverage']) ? esc_html_e('Applicable to Total Merchandise Price and Handling Fee','usces'  ) : esc_html_e('Limited Only to Total Merchandise Price','usces'  ); ?></td>
    </tr>

    <tr>
        <td data-export-label="Timing point of grant"><?php esc_html_e( 'Timing point of grant', 'usces' ); ?>:</td>
        <td><?php echo ($welcart_information['point_assign']) ? esc_html_e('Payment at the time','usces'  ) : esc_html_e('Immediately','usces'  ); ?></td>
    </tr>
    </tbody>
</table>
<table class="wc_status_table widefat" cellspacing="0" id="payment_methods_status">
    <thead>
    <tr>
        <th colspan="3" data-export-label="payment method"><h2><?php esc_html_e( 'payment method', 'usces' ); ?></h2></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><strong><?php echo esc_html_e( 'A payment method name', 'usces' ); ?></strong></td>
        <td><strong><?php echo esc_html_e( 'Type of payment', 'usces' ); ?></strong></td>
        <td><strong><?php echo esc_html_e( 'Usage conditions', 'usces' ); ?></strong></td>
    </tr>
    <?php foreach ($welcart_information['payment_methods'] as $payment){ ?>
        <tr>
            <td><?php echo esc_html( $payment['name'] ); ?></td>
            <td><?php echo (isset($welcart_information['payment_structure'][$payment['settlement']])) ? (esc_html( $welcart_information['payment_structure'][$payment['settlement']])) : ""; ?> </td>
            <td><?php echo ($payment['use'] == 'activate') ? esc_html_e('Activate','usces'  ) : esc_html_e('Deactivate','usces'  ); ?> </td>
        </tr>
    <?php } ?>
    </tbody>
</table>
<table class="wc_status_table widefat" cellspacing="0" id="common_options_status">
    <thead>
    <tr>
        <th colspan="4" data-export-label="Common Options"><h2><?php esc_html_e( 'Common Options', 'usces' ); ?></h2></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><strong><?php echo esc_html_e( 'option name', 'usces' ); ?></strong></td>
        <td><strong><?php echo esc_html_e( 'selected amount', 'usces' ); ?></strong></td>
        <td><strong><?php echo esc_html_e( 'Type of option', 'usces' ); ?></strong></td>
        <td><strong><?php echo esc_html_e( 'Required', 'usces' ); ?></strong></td>
    </tr>
    <?php foreach ($welcart_information['common_options'] as $option){ ?>
        <tr>
            <td><?php echo esc_html( $option['name'] ); ?></td>
            <td><?php echo esc_html( $option['value'] ); ?></td>
            <td><?php echo (isset($welcart_information['common_options_type'][$option['means']])) ? (esc_html( $welcart_information['common_options_type'][$option['means']])) : ""; ?> </td>
            <td><?php echo ($option['essential']) ? esc_html_e('Essential','usces'  ) : '&nbsp;'; ?> </td>
        </tr>
    <?php } ?>
    </tbody>
</table>
<!--<table class="wc_status_table widefat" cellspacing="0" id="">-->
<!--    <thead>-->
<!--    <tr>-->
<!--        <th colspan="3" data-export-label="Shipping"><h2>--><?php //esc_html_e( 'Shipping Setting', 'usces' ); ?><!--</h2></th>-->
<!--    </tr>-->
<!--    </thead>-->
<!--    <tbody>-->
<!--    <tr>-->
<!--        <td data-export-label="Deadline for Delivery">--><?php //esc_html_e( 'Deadline for Delivery', 'usces' ); ?><!--:</td>-->
<!--        <td>--><?php //echo (isset($welcart_information['delivery_time_limit']['hour'])) ?  esc_html($welcart_information['delivery_time_limit']['hour']) : "00"; ?><!-- - --><?php //echo (isset($welcart_information['delivery_time_limit']['min'])) ?  esc_html($welcart_information['delivery_time_limit']['min']) : "00"; ?><!-- </td>-->
<!--    </tr>-->
<!--    <tr>-->
<!--        <td data-export-label="Morning Delivery Option">--><?php //esc_html_e( 'Morning Delivery Option', 'usces' ); ?><!--:</td>-->
<!--        <td>-->
<!--        --><?php
//            switch ($welcart_information['shortest_delivery_time']){
//                case 1:
//                    echo esc_html_e('Morning Delivery', 'usces');
//                    break;
//                case 2:
//                    echo esc_html_e('No Morning Delivery', 'usces');
//                    break;
//                default:
//                    echo esc_html_e('Do Not Apply', 'usces');
//                    break;
//            }
//        ?>
<!--        </td>-->
<!--    </tr>-->
<!--    <tr>-->
<!--        <td data-export-label="Set Delivery Dates">--><?php //esc_html_e( 'Set Delivery Dates', 'usces' ); ?><!--:</td>-->
<!--        <td>--><?php //echo esc_html($welcart_information['delivery_after_days']);?><!-- </td>-->
<!--    </tr>-->
<!--    </tbody>-->
<!--</table>-->
<table class="wc_status_table widefat" cellspacing="0" id="shipping_method_status">
    <thead>
    <tr>
        <th colspan="6" data-export-label="shipping option"><h2><?php esc_html_e( 'shipping option', 'usces' ); ?></h2></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><strong><?php echo esc_html_e( 'Shipping name', 'usces' ); ?></strong></td>
        <td><strong><?php echo esc_html_e( 'Deliverly time', 'usces' ); ?></strong></td>
        <td><strong><?php echo esc_html_e( 'Possible Delivery Area', 'usces' ); ?></strong></td>
        <td><strong><?php echo esc_html_e( 'Postage fixation', 'usces' ); ?></strong></td>
        <td><strong><?php echo esc_html_e( 'Delivery Days', 'usces' ); ?></strong></td>
        <td><strong><?php echo esc_html_e( 'No COD', 'usces' ); ?></strong></td>
    </tr>
    <?php foreach ($welcart_information['shipping_methods'] as $shipping){
        $charge = '';
        $delivery_day = '';
        if($shipping['charge'] == -1){
            $charge = esc_html__('Not fixing shipping.', 'usces');
        }
        else{
            foreach ($welcart_information['shipping_charge'] as $shipping_charge){
                if($shipping['charge'] == $shipping_charge['id']){
                    $charge = $shipping_charge['name'];
                    break;
                }
            }
        }
        if($shipping['days'] == -1){
            $delivery_day = esc_html__('Delivery Date Not Specified', 'usces');
        }
        else{
            foreach ($welcart_information['delivery_days'] as $day){
                if($shipping['days'] == $day['id']){
                    $delivery_day = $day['name'];
                    break;
                }
            }
        }
        ?>
        <tr>
            <td><?php echo esc_html( $shipping['name'] ); ?></td>
            <td><?php echo esc_html( $shipping['time'] ); ?></td>
            <td><?php echo ($shipping['intl']) ? esc_html_e('International Shipment','usces'  ) : esc_html_e('Domestic Shipment','usces'  ); ?> </td>
            <td><?php echo esc_html($charge); ?> </td>
            <td><?php echo esc_html($delivery_day); ?> </td>
            <td><?php echo ($shipping['nocod']) ? esc_html_e('not available','usces'  ) : '&nbsp;'; ?> </td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<table class="wc_status_table widefat" cellspacing="0" id="">
    <thead>
    <tr>
        <th colspan="3" data-export-label="Shipping"><h2><?php esc_html_e( 'Shipping', 'usces' ); ?></h2></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><strong><?php echo esc_html_e( 'Shipping charge name', 'usces' ); ?></strong></td>
    </tr>
    <?php foreach ($welcart_information['shipping_charge'] as $shipping_charge){ ?>
        <tr>
            <td><?php echo esc_html( $shipping_charge['name'] ); ?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<table class="wc_status_table widefat" cellspacing="0" >
    <thead>
    <tr>
        <th colspan="2" data-export-label="Mail Options"><h2><?php esc_html_e( 'Mail Options', 'usces' ); ?></h2></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td data-export-label="New sign-in completion email"><?php esc_html_e( 'New sign-in completion email', 'usces' ); ?>:</td>
        <td><?php echo ($welcart_information['newmem_admin_mail']) ?  esc_html_e('Send', 'usces') : esc_html_e('Don\'t send', 'usces'); ?></td>
    </tr>
    <tr>
        <td data-export-label="Member update completion email to admin"><?php esc_html_e( 'Member update completion email to admin', 'usces' ); ?>:</td>
        <td><?php echo ($welcart_information['updmem_admin_mail']) ?  esc_html_e('Send', 'usces') : esc_html_e('Don\'t send', 'usces'); ?></td>
    </tr>
    <tr>
        <td data-export-label="Member update completion email to customer"><?php esc_html_e( 'Member update completion email to customer', 'usces' ); ?>:</td>
        <td><?php echo ($welcart_information['updmem_customer_mail']) ?  esc_html_e('Send', 'usces') : esc_html_e('Don\'t send', 'usces'); ?></td>
    </tr>
    <tr>
        <td data-export-label="Member removal completion email to admin"><?php esc_html_e( 'Member removal completion email to admin', 'usces' ); ?>:</td>
        <td><?php echo ($welcart_information['delmem_admin_mail']) ?  esc_html_e('Send', 'usces') : esc_html_e('Don\'t send', 'usces'); ?></td>
    </tr>
    <tr>
        <td data-export-label="Member removal completion email to customer"><?php esc_html_e( 'Member removal completion email to customer', 'usces' ); ?>:</td>
        <td><?php echo ($welcart_information['delmem_customer_mail']) ?  esc_html_e('Send', 'usces') : esc_html_e('Don\'t send', 'usces'); ?></td>
    </tr>
    <tr>
        <td data-export-label="Customer name"><?php esc_html_e( 'Customer name', 'usces' ); ?>:</td>
        <td><?php echo ($welcart_information['put_customer_name']) ?  esc_html_e('Indication', 'usces') : esc_html_e('Non-indication', 'usces'); ?></td>
    </tr>
    </tbody>
</table>

<table class="wc_status_table widefat" cellspacing="0" >
    <thead>
    <tr>
        <th colspan="3" data-export-label="Rule of the column for a item name"><h2><?php esc_html_e( 'Rule of the column for a item name', 'usces' ); ?></h2></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($welcart_information['indi_item_name'] as $key => $value){ ?>
        <tr>
            <td><?php echo esc_html_e("Indication of {$welcart_information['indi_item_name_text'][$key]}", 'usces'); ?> : <?php echo ($value) ? esc_html_e('Show','usces'  ) : '&nbsp;'; ?></td>
            <td><?php echo esc_html_e("Position of {$welcart_information['indi_item_name_text'][$key]}", 'usces'); ?> : <?php echo esc_html($welcart_information['pos_item_name'][$key]); ?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<table class="wc_status_table widefat" cellspacing="0" >
    <thead>
    <tr>
        <th colspan="2" data-export-label="System Setting"><h2><?php esc_html_e( 'System Setting', 'usces' ); ?></h2></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td data-export-label="Display Modes"><?php esc_html_e( 'Display Modes', 'usces' ); ?>:</td>
        <td><?php echo ($welcart_information['divide_item']) ? esc_html_e('Not display an article in blog', 'usces') : '&nbsp;'; ?></td>
    </tr>
	<tr>
		<td data-export-label="rel attribute"><?php esc_html_e( 'rel attribute', 'usces' ); ?>:</td>
		<td><?php echo ( isset( $welcart_information['itemimg_anchor_rel'] ) ) ? $welcart_information['itemimg_anchor_rel'] : '&nbsp;'; ?></td>
	</tr>
	<tr>
		<td data-export-label="compound category sort item"><?php esc_html_e( 'compound category sort item', 'usces' ); ?>:</td>
		<td><?php echo ( 'name' == $welcart_information['fukugo_category_orderby'] ) ? esc_html_e('category name', 'usces') : esc_html_e('category ID', 'usces'); ?></td>
	</tr>
	<tr>
		<td data-export-label="compound category sort order"><?php esc_html_e( 'compound category sort order', 'usces' ); ?>:</td>
		<td><?php echo ( 'DESC' == $welcart_information['fukugo_category_order'] ) ? esc_html_e( 'Descendin', 'usces' ) : esc_html_e( 'Ascending', 'usces' ); ?></td>
	</tr>
    <tr>
        <td data-export-label="Switching SSL"><?php esc_html_e( 'Switching SSL', 'usces' ); ?>:</td>
        <td><?php echo ($welcart_information['use_ssl']) ?  esc_html_e('Switching', 'usces') : '&nbsp;'; ?></td>
    </tr>
    <tr>
        <td data-export-label="The page_id of the inquiry-form"><?php esc_html_e( 'The page_id of the inquiry-form', 'usces' ); ?>:</td>
        <td><?php echo esc_html($welcart_information['inquiry_id']); ?></td>
    </tr>
    <tr>
        <td data-export-label="To disable usces_cart.css"><?php esc_html_e( 'To disable usces_cart.css', 'usces' ); ?>:</td>
        <td><?php echo ($welcart_information['no_cart_css']) ?  esc_html_e('To disable', 'usces') : '&nbsp;'; ?></td>
    </tr>
    <tr>
        <td data-export-label="Product sub-image rule"><?php esc_html_e( 'Product sub-image rule', 'usces' ); ?>:</td>
        <td><?php echo ($welcart_information['subimage_rule']) ?  _e("apply the new rule<br />(Tow underscores between the Product Code and serial number)", 'usces') : _e("not apply the new rule<br />(Truncation Product Code)", 'usces'); ?></td>
    </tr>
    <tr>
        <td data-export-label="Described method of invoice"><?php esc_html_e( 'Described method of invoice', 'usces' ); ?>:</td>
        <td><?php echo ($welcart_information['pdf_delivery']) ?  esc_html_e('To address the shipping information', 'usces') : esc_html_e('To address the purchaser information', 'usces'); ?></td>
    </tr>

    <tr>
        <td data-export-label="Character code in the CSV file"><?php esc_html_e( 'Character code in the CSV file', 'usces' ); ?>:</td>
        <td><?php echo ($welcart_information['csv_encode_type']) ?  esc_html_e('UTF-8', 'usces') : esc_html_e('Sift-JIS', 'usces'); ?></td>
    </tr>

    <tr>
        <td data-export-label="'Category' of CSV product data file"><?php esc_html_e( "'Category' of CSV product data file", 'usces' ); ?>:</td>
        <td><?php echo ($welcart_information['csv_category_format']) ?  esc_html_e('slug (slug)', 'usces') : esc_html_e('ID (tag_ID)', 'usces'); ?></td>
    </tr>
    <tr>
        <td data-export-label="Order data re-created from the settlement log"><?php esc_html_e( 'Order data re-created from the settlement log', 'usces' ); ?>:</td>
        <td><?php echo ($welcart_information['settlement_backup']) ?  esc_html_e('Use', 'usces') : esc_html_e('Do not Use', 'usces'); ?></td>
    </tr>

    <tr>
        <td data-export-label="Settlement error message"><?php esc_html_e( 'Settlement error message', 'usces' ); ?>:</td>
        <td><?php echo ($welcart_information['settlement_notice']) ?  esc_html_e('Display', 'usces') : esc_html_e('Do not display', 'usces'); ?></td>
    </tr>
    <tr>
        <td data-export-label="The language of the front-end"><?php esc_html_e( 'The language of the front-end', 'usces' ); ?>:</td>
        <td><?php echo esc_html($welcart_information['front_lang']); ?></td>
    </tr>
    <tr>
        <td data-export-label="Currencies"><?php esc_html_e( 'Currencies', 'usces' ); ?>:</td>
        <td><?php echo (isset($welcart_information['country'][$welcart_information['currency']])) ? esc_html($welcart_information['country'][$welcart_information['currency']]) : esc_html($welcart_information['currency']) ?></td>
    </tr>

    <tr>
        <td data-export-label="The name and address form"><?php esc_html_e( 'The name and address form', 'usces' ); ?>:</td>
        <td><?php echo (isset($welcart_information['country'][$welcart_information['addressform']])) ? esc_html($welcart_information['country'][$welcart_information['addressform']]) : esc_html($welcart_information['currency']) ?></td>
    </tr>
    <tr>
        <td data-export-label="Target Market"><?php esc_html_e( 'Target Market', 'usces' ); ?>:</td>
        <td>
            <?php
            $target_market_country = [];
            foreach ($welcart_information['target_market'] as $key => $value){
                if(isset($welcart_information['country'][$value])){
                    $target_market_country[] = $welcart_information['country'][$value];
                }
            }
            echo  esc_html(implode(', ', $target_market_country));
            ?>
        </td>
    </tr>
    </tbody>
</table>
<table class="wc_status_table widefat" cellspacing="0">
    <thead>
    <tr>
        <th colspan="2" data-export-label="System extension"><h2><?php esc_html_e( 'System extension', 'usces' ); ?></h2></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td data-export-label="Ganbare Tencho"><?php esc_html_e( 'Ganbare Tencho', 'usces' ); ?>:</td>
        <td><?php echo ($welcart_information['ganbare_activate_flag']) ?  esc_html_e('enable', 'usces') : esc_html_e('disable', 'usces'); ?></td>
    </tr>
    <tr>
        <td data-export-label="Linkage Order Upadate"><?php esc_html_e( 'Linkage Order Upadate', 'usces' ); ?>:</td>
        <td><?php echo ($welcart_information['stocklink_orderedit_flag']) ?  esc_html_e('enable', 'usces') : esc_html_e('disable', 'usces'); ?></td>
    </tr>
    <tr>
        <td data-export-label="Linkage Order Collective Upadate"><?php esc_html_e( 'Linkage Order Collective Upadate', 'usces' ); ?>:</td>
        <td><?php echo ($welcart_information['stocklink_collective_flag']) ?  esc_html_e('enable', 'usces') : esc_html_e('disable', 'usces'); ?></td>
    </tr>
    <tr>
        <td data-export-label="[New] Order List"><?php esc_html_e( '[New] Order List', 'usces' ); ?>:</td>
        <td><?php echo ($welcart_information['datalistup_orderlist_flag']) ?  esc_html_e('enable', 'usces') : esc_html_e('disable', 'usces'); ?></td>
    </tr>
    <tr>
        <td data-export-label="[New] Member List"><?php esc_html_e( '[New] Member List', 'usces' ); ?>:</td>
        <td><?php echo ($welcart_information['datalistup_memberlist_flag']) ?  esc_html_e('enable', 'usces') : esc_html_e('disable', 'usces'); ?></td>
    </tr>
    <tr>
        <td data-export-label="Verify New Member Email"><?php esc_html_e( 'Verify New Member Email', 'usces' ); ?>:</td>
        <td><?php echo ($welcart_information['verifyemail_switch_flag']) ?  esc_html_e('enable', 'usces') : esc_html_e('disable', 'usces'); ?></td>
    </tr>

    </tbody>
</table >
<table class="wc_status_table widefat" cellspacing="0">
    <thead>
    <tr>
        <th colspan="2" data-export-label="Settlement Setting"><h2><?php esc_html_e( 'Settlement Setting', 'usces' ); ?></h2></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td data-export-label="Settlement modules in use"><?php esc_html_e( 'Settlement modules in use', 'usces' ); ?>:</td>
        <td>
            <?php
            $settlement_selected_text = [];
            if(is_array($welcart_information['settlement_selected'])){
                foreach ($welcart_information['settlement_selected'] as $key => $value){
                    if(isset($welcart_information['available_settlement'][$value])){
                        $settlement_selected_text[] = esc_html__($welcart_information['available_settlement'][$value], 'usces');
                    }
                }
            }
            echo  esc_html(implode(', ', $settlement_selected_text));
            ?>
        </td>
    </tr>
    </tbody>
</table>

<table class="wc_status_table widefat" cellspacing="0">
    <thead>
    <tr>
        <th colspan="2" data-export-label="Management Information"><h2><?php esc_html_e( 'Management Information', 'usces' ); ?></h2></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td data-export-label="Number of orders"><?php esc_html_e( 'Number of orders', 'usces' ); ?>:</td>
        <td><?php echo esc_html($welcart_information['number_of_orders']); ?></td>
    </tr>
    <tr>
        <td data-export-label="Number of members"><?php esc_html_e( 'Number of members', 'usces' ); ?>:</td>
        <td><?php echo esc_html($welcart_information['number_of_members']); ?></td>
    </tr>
    </tbody>
</table>

<table class="wc_status_table widefat" cellspacing="0">
    <thead>
    <tr>
        <th colspan="2" data-export-label="Server environment"><h2><?php esc_html_e( 'Server environment', 'usces' ); ?></h2></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td data-export-label="Server"><?php esc_html_e( 'Server', 'usces' ); ?>:</td>
        <td><?php echo esc_html($environment['server']); ?></td>
    </tr>
    <tr>
        <td data-export-label="PHP version"><?php esc_html_e( 'PHP version', 'usces' ); ?>:</td>
        <td><?php echo esc_html($environment['php_version']); ?></td>
    </tr>
    <tr>
        <td data-export-label="PHP global memory"><?php esc_html_e( 'PHP global memory', 'usces' ); ?>:</td>
        <td><?php echo esc_html($ini_confs['memory_limit']['global_value']); ?></td>
    </tr>
    <tr>
        <td data-export-label="PHP locale memory"><?php esc_html_e( 'PHP locale memory', 'usces' ); ?>:</td>
        <td><?php echo esc_html($ini_confs['memory_limit']['local_value']); ?></td>
    </tr>
    <tr>
        <td data-export-label="PHP post max size"><?php esc_html_e( 'PHP post max size', 'usces' ); ?>:</td>
        <td><?php echo esc_html($environment['php_post_max_size']); ?></td>
    </tr>
    <tr>
        <td data-export-label="PHP time limit"><?php esc_html_e( 'PHP time limit', 'usces' ); ?>:</td>
        <td><?php echo esc_html($environment['php_max_execution_time']); ?></td>
    </tr>
    <tr>
        <td data-export-label="PHP Max Input Vars"><?php esc_html_e( 'PHP Max Input Vars', 'usces' ); ?>:</td>
        <td><?php echo esc_html($environment['php_max_input_vars']); ?></td>
    </tr>
    <tr>
    <tr>
        <td data-export-label="cURL"><?php esc_html_e( 'cURL', 'usces' ); ?>:</td>
        <td><?php echo (in_array('curl', $loaded_extensions) ? 'ON' : '-'); ?></td>
    </tr>
        <td data-export-label="cURL version"><?php esc_html_e( 'cURL version', 'usces' ); ?>:</td>
        <td><?php echo esc_html($environment['curl_version']['version'] . ' ' . $environment['curl_version']['host'] ); ?></td>
    </tr>
    <tr>
        <td data-export-label="MySQL version"><?php esc_html_e( 'MySQL version', 'usces' ); ?>:</td>
        <td><?php echo esc_html($environment['mysql_version']); ?></td>
    </tr>
    <tr>
        <td data-export-label="Maximum upload size"><?php esc_html_e( 'Maximum upload size', 'usces' ); ?>:</td>
        <td><?php echo esc_html($environment['upload_max_filesize']); ?></td>
    </tr>
    <tr>
        <td data-export-label="SimpleXML"><?php esc_html_e( 'SimpleXML', 'usces' ); ?>:</td>
        <td><?php echo (in_array('SimpleXML', $loaded_extensions) ? 'ON' : '-'); ?></td>
    </tr>
    <tr>
        <td data-export-label="gd"><?php esc_html_e( 'gd', 'usces' ); ?>:</td>
        <td><?php echo (in_array('gd', $loaded_extensions) ? 'ON' : '-'); ?></td>
    </tr>
    <tr>
        <td data-export-label="json"><?php esc_html_e( 'json', 'usces' ); ?>:</td>
        <td><?php echo (in_array('json', $loaded_extensions) ? 'ON' : '-'); ?></td>
    </tr>
    <tr>
        <td data-export-label="mbstring"><?php esc_html_e( 'mbstring', 'usces' ); ?>:</td>
        <td><?php echo (in_array('mbstring', $loaded_extensions) ? 'ON' : '-'); ?></td>
    </tr>
    <tr>
        <td data-export-label="openssl"><?php esc_html_e( 'openssl', 'usces' ); ?>:</td>
        <td><?php echo (in_array('openssl', $loaded_extensions) ? 'ON' : '-'); ?></td>
    </tr>
    </tbody>
</table>
