<?php
/**
 * SBペイメントサービス
 *
 * @package  Welcart
 * @author   Collne Inc.
 * @version  1.0.0
 * @since    1.9.16
 */
class SBPS_SETTLEMENT extends SBPS_MAIN {

	/**
	 * Instance of this class.
	 *
	 * @var SBPS_SETTLEMENT
	 */
	protected static $instance = null;

	/**
	 * Construct.
	 */
	public function __construct() {

		$this->acting_name = 'SBPS';
		$this->acting_formal_name = 'SBペイメントサービス';

		$this->acting_card = 'sbps_card';
		$this->acting_conv = 'sbps_conv';
		$this->acting_payeasy = 'sbps_payeasy';
		$this->acting_wallet = 'sbps_wallet';
		$this->acting_mobile = 'sbps_mobile';
		$this->acting_paypay = 'sbps_paypay';

		$this->acting_flg_card = 'acting_sbps_card';
		$this->acting_flg_conv = 'acting_sbps_conv';
		$this->acting_flg_payeasy = 'acting_sbps_payeasy';
		$this->acting_flg_wallet = 'acting_sbps_wallet';
		$this->acting_flg_mobile = 'acting_sbps_mobile';
		$this->acting_flg_paypay = 'acting_sbps_paypay';

		$this->pay_method = array(
			'acting_sbps_card',
			'acting_sbps_conv',
			'acting_sbps_payeasy',
			'acting_sbps_wallet',
			'acting_sbps_mobile',
			'acting_sbps_paypay',
		);

		parent::__construct( 'sbps' );

		if ( $this->is_activate_card() || $this->is_activate_paypay() ) {
			add_action( 'usces_after_cart_instant', array( $this, 'acting_notice' ) );
			if ( is_admin() ) {
				add_action( 'usces_action_admin_ajax', array( $this, 'admin_ajax' ) );
				add_filter( 'usces_filter_orderlist_detail_value', array( $this, 'orderlist_settlement_status' ), 10, 4 );
				add_action( 'usces_action_order_edit_form_status_block_middle', array( $this, 'settlement_status' ), 10, 3 );
				add_action( 'usces_action_order_edit_form_settle_info', array( $this, 'settlement_information' ), 10, 2 );
				add_action( 'usces_action_endof_order_edit_form', array( $this, 'settlement_dialog' ), 10, 2 );
			}
		}

		$this->initialize_data();
	}

	/**
	 * Return an instance of this class.
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Initialize
	 */
	public function initialize_data() {
		$options = get_option( 'usces' );
		// if ( ! isset( $options['acting_settings'] ) || ! isset( $options['acting_settings']['sbps'] ) ) {
			$options['acting_settings']['sbps']['merchant_id'] = ( isset( $options['acting_settings']['sbps']['merchant_id'] ) ) ? $options['acting_settings']['sbps']['merchant_id'] : '';
			$options['acting_settings']['sbps']['service_id'] = ( isset( $options['acting_settings']['sbps']['service_id'] ) ) ? $options['acting_settings']['sbps']['service_id'] : '';
			$options['acting_settings']['sbps']['hash_key'] = ( isset( $options['acting_settings']['sbps']['hash_key'] ) ) ? $options['acting_settings']['sbps']['hash_key'] : '';
			$options['acting_settings']['sbps']['ope'] = ( isset( $options['acting_settings']['sbps']['ope'] ) ) ? $options['acting_settings']['sbps']['ope'] : '';
			$options['acting_settings']['sbps']['send_url'] = ( isset( $options['acting_settings']['sbps']['send_url'] ) ) ? $options['acting_settings']['sbps']['send_url'] : '';
			$options['acting_settings']['sbps']['send_url_check'] = ( isset( $options['acting_settings']['sbps']['send_url_check'] ) ) ? $options['acting_settings']['sbps']['send_url_check'] : '';
			$options['acting_settings']['sbps']['send_url_test'] = ( isset( $options['acting_settings']['sbps']['send_url_test'] ) ) ? $options['acting_settings']['sbps']['send_url_test'] : '';
			$options['acting_settings']['sbps']['card_activate'] = ( isset( $options['acting_settings']['sbps']['card_activate'] ) ) ? $options['acting_settings']['sbps']['card_activate'] : 'off';
			$options['acting_settings']['sbps']['3d_secure'] = ( isset( $options['acting_settings']['sbps']['3d_secure'] ) ) ? $options['acting_settings']['sbps']['3d_secure'] : 'off';
			$options['acting_settings']['sbps']['cust_manage'] = ( isset( $options['acting_settings']['sbps']['cust_manage'] ) ) ? $options['acting_settings']['sbps']['cust_manage'] : 'off';
			$options['acting_settings']['sbps']['sales'] = ( isset( $options['acting_settings']['sbps']['sales'] ) ) ? $options['acting_settings']['sbps']['sales'] : 'manual';
			$options['acting_settings']['sbps']['3des_key'] = ( isset( $options['acting_settings']['sbps']['3des_key'] ) ) ? $options['acting_settings']['sbps']['3des_key'] : '';
			$options['acting_settings']['sbps']['3desinit_key'] = ( isset( $options['acting_settings']['sbps']['3desinit_key'] ) ) ? $options['acting_settings']['sbps']['3desinit_key'] : '';
			$options['acting_settings']['sbps']['basic_id'] = ( isset( $options['acting_settings']['sbps']['basic_id'] ) ) ? $options['acting_settings']['sbps']['basic_id'] : '';
			$options['acting_settings']['sbps']['basic_password'] = ( isset( $options['acting_settings']['sbps']['basic_password'] ) ) ? $options['acting_settings']['sbps']['basic_password'] : '';
			$options['acting_settings']['sbps']['conv_activate'] = ( isset( $options['acting_settings']['sbps']['conv_activate'] ) ) ? $options['acting_settings']['sbps']['conv_activate'] : 'off';
			$options['acting_settings']['sbps']['payeasy_activate'] = ( isset( $options['acting_settings']['sbps']['payeasy_activate'] ) ) ? $options['acting_settings']['sbps']['payeasy_activate'] : 'off';
			$options['acting_settings']['sbps']['wallet_yahoowallet'] = ( isset( $options['acting_settings']['sbps']['wallet_yahoowallet'] ) ) ? $options['acting_settings']['sbps']['wallet_yahoowallet'] : 'off';
			$options['acting_settings']['sbps']['wallet_rakuten'] = ( isset( $options['acting_settings']['sbps']['wallet_rakuten'] ) ) ? $options['acting_settings']['sbps']['wallet_rakuten'] : 'off';
			$options['acting_settings']['sbps']['wallet_paypal'] = ( isset( $options['acting_settings']['sbps']['wallet_paypal'] ) ) ? $options['acting_settings']['sbps']['wallet_paypal'] : 'off';
			$options['acting_settings']['sbps']['wallet_netmile'] = 'off';
			$options['acting_settings']['sbps']['wallet_alipay'] = ( isset( $options['acting_settings']['sbps']['wallet_alipay'] ) ) ? $options['acting_settings']['sbps']['wallet_alipay'] : 'off';
			$options['acting_settings']['sbps']['wallet_activate'] = ( isset( $options['acting_settings']['sbps']['wallet_activate'] ) ) ? $options['acting_settings']['sbps']['wallet_activate'] : 'off';
			$options['acting_settings']['sbps']['mobile_docomo'] = ( isset( $options['acting_settings']['sbps']['mobile_docomo'] ) ) ? $options['acting_settings']['sbps']['mobile_docomo'] : 'off';
			$options['acting_settings']['sbps']['mobile_auone'] = ( isset( $options['acting_settings']['sbps']['mobile_auone'] ) ) ? $options['acting_settings']['sbps']['mobile_auone'] : 'off';
			$options['acting_settings']['sbps']['mobile_mysoftbank'] = 'off';
			$options['acting_settings']['sbps']['mobile_softbank2'] = ( isset( $options['acting_settings']['sbps']['mobile_softbank2'] ) ) ? $options['acting_settings']['sbps']['mobile_softbank2'] : 'off';
			$options['acting_settings']['sbps']['mobile_activate'] = ( isset( $options['acting_settings']['sbps']['mobile_activate'] ) ) ? $options['acting_settings']['sbps']['mobile_activate'] : 'off';
			$options['acting_settings']['sbps']['paypay_activate'] = ( isset( $options['acting_settings']['sbps']['paypay_activate'] ) ) ? $options['acting_settings']['sbps']['paypay_activate'] : 'off';
			$options['acting_settings']['sbps']['paypay_sales'] = ( isset( $options['acting_settings']['sbps']['paypay_sales'] ) ) ? $options['acting_settings']['sbps']['paypay_sales'] : 'manual';
			update_option( 'usces', $options );
		// }

		$available_settlement = get_option( 'usces_available_settlement' );
		if ( ! in_array( 'sbps', $available_settlement ) ) {
			$available_settlement['sbps'] = $this->acting_formal_name;
			update_option( 'usces_available_settlement', $available_settlement );
		}

		$noreceipt_status = get_option( 'usces_noreceipt_status' );
		if ( ! in_array( 'acting_sbps_conv', $noreceipt_status ) || ! in_array( 'acting_sbps_payeasy', $noreceipt_status ) ) {
			$noreceipt_status[] = 'acting_sbps_conv';
			$noreceipt_status[] = 'acting_sbps_payeasy';
			update_option( 'usces_noreceipt_status', $noreceipt_status );
		}

		$this->unavailable_method = array( 'acting_dsk_card', 'acting_dsk_conv', 'acting_dsk_payeasy' );
	}

	/**
	 * Admin script.
	 * admin_print_footer_scripts
	 */
	public function admin_scripts() {
		global $usces;

		$admin_page = ( isset( $_GET['page'] ) ) ? wp_unslash( $_GET['page'] ) : '';
		switch ( $admin_page ) :
			case 'usces_settlement':
				$settlement_selected = get_option( 'usces_settlement_selected' );
				if ( in_array( $this->paymod_id, (array) $settlement_selected ) ) :
					$acting_opts = $this->get_acting_settings();
					?>
<script type="text/javascript">
jQuery( document ).ready( function( $ ) {
	var sbps_card_activate = "<?php echo esc_js( $acting_opts['card_activate'] ); ?>";
	var sbps_paypay_activate = "<?php echo esc_js( $acting_opts['paypay_activate'] ); ?>";
	if ( "token" == sbps_card_activate ) {
		$( ".card_link_sbps" ).css( "display", "none" );
		$( ".card_link_token_sbps" ).css( "display", "" );
		$( ".card_token_sbps" ).css( "display", "" );
	} else if ( "on" == sbps_card_activate ) {
		$( ".card_link_sbps" ).css( "display", "" );
		$( ".card_link_token_sbps" ).css( "display", "" );
		$( ".card_token_sbps" ).css( "display", "none" );
	} else {
		$( ".card_link_sbps" ).css( "display", "none" );
		$( ".card_link_token_sbps" ).css( "display", "none" );
		$( ".card_token_sbps" ).css( "display", "none" );
	}

	$( document ).on( "change", ".card_activate_sbps", function() {
		if ( "token" == $( this ).val() ) {
			$( ".card_link_sbps" ).css( "display", "none" );
			$( ".card_link_token_sbps" ).css( "display", "" );
			$( ".card_token_sbps" ).css( "display", "" );
		} else if ( "on" == $( this ).val() ) {
			$( ".card_link_sbps" ).css( "display", "" );
			$( ".card_link_token_sbps" ).css( "display", "" );
			$( ".card_token_sbps" ).css( "display", "none" );
		} else {
			$( ".card_link_sbps" ).css( "display", "none" );
			$( ".card_link_token_sbps" ).css( "display", "none" );
			$( ".card_token_sbps" ).css( "display", "none" );
		}
	});

	if ( "on" == sbps_paypay_activate ) {
		$( ".paypay_sbps" ).css( "display", "" );
	} else {
		$( ".paypay_sbps" ).css( "display", "none" );
	}

	$( document ).on( "change", ".paypay_activate_sbps", function() {
		if ( "on" == $( this ).val() ) {
			$( ".paypay_sbps" ).css( "display", "" );
		} else {
			$( ".paypay_sbps" ).css( "display", "none" );
		}
	});
});
</script>
					<?php
				endif;
				break;

			case 'usces_orderlist':
			case 'usces_continue':
				$acting_flg = '';
				$dialog_title = '';
				$order_id = '';
				$order_data = array();

				/* 受注編集画面・継続課金会員詳細画面 */
				if ( 'usces_orderlist' == $admin_page && ( isset( $_GET['order_action'] ) && ( 'edit' == $_GET['order_action'] || 'editpost' == $_GET['order_action'] || 'newpost' == $_GET['order_action'] ) ) ||
					'usces_continue' == $admin_page && ( isset( $_GET['continue_action'] ) && 'settlement' == $_GET['continue_action'] ) ) {
					$order_id = ( isset( $_GET['order_id'] ) ) ? $_GET['order_id'] : '';
					if ( empty( $order_id ) && isset( $_POST['order_id'] ) ) $order_id = $_POST['order_id'];
					if ( empty( $order_id ) && isset( $_REQUEST['order_id'] ) ) $order_id = $_REQUEST['order_id'];
					if ( ! empty( $order_id ) ) {
						$order_data = $usces->get_order_data( $order_id, 'direct' );
						$payment = usces_get_payments_by_name( $order_data['order_payment_name'] );
						if ( isset( $payment['settlement'] ) ) {
							$acting_flg = $payment['settlement'];
						}
						if ( isset( $payment['name'] ) ) {
							$dialog_title = $payment['name'];
						}
					}
				}
				$args = compact( 'order_id', 'acting_flg', 'admin_page', 'order_data' );

				if ( 'acting_sbps_card' == $acting_flg || 'acting_sbps_paypay' == $acting_flg ) :
					$acting_opts = $this->get_acting_settings();
					?>
<script type="text/javascript">
jQuery( document ).ready( function( $ ) {
	adminOrderEdit = {
					<?php
					/* クレジットカード */
					if ( 'acting_sbps_card' == $acting_flg ) :
						?>
		getSettlementInfoCard : function() {
			$( "#settlement-response" ).html( "" );
			$( "#settlement-response-loading" ).html( '<img src="' + uscesL10n.USCES_PLUGIN_URL + '/images/loading.gif" />' );
			var mode = ( "" != $( "#error" ).val() ) ? "error_sbps_card" : "get_sbps_card";
			$.ajax({
				url: ajaxurl,
				type: "POST",
				cache: false,
				dataType: 'json',
				data: {
					action: "usces_admin_ajax",
					mode: mode,
					order_id: $( "#order_id" ).val(),
					tracking_id: $( "#tracking_id" ).val(),
					member_id: $( "#member_id" ).val(),
					wc_nonce: $( "#wc_nonce" ).val()
				}
			}).done( function( retVal, dataType ) {
				$( "#settlement-response" ).html( retVal.result );
				if ( $( "#refund-settlement" ).length ) {
					$( "#refund-settlement" ).prop( "disabled", true );
				}
				$( "#settlement-response-loading" ).html( "" );
			}).fail( function( jqXHR, textStatus, errorThrown ) {
				console.log( textStatus );
				console.log( jqXHR.status );
				console.log( errorThrown.message );
				$( "#settlement-response-loading" ).html( "" );
			});
			return false;
		},
						<?php
						/* 指定売上 */
						if ( 'manual' == $acting_opts['sales'] ) :
							?>
		salesSettlementCard : function( amount ) {
			$( "#settlement-response" ).html( "" );
			$( "#settlement-response-loading" ).html( '<img src="' + uscesL10n.USCES_PLUGIN_URL + '/images/loading.gif" />' );
			$.ajax({
				url: ajaxurl,
				type: "POST",
				cache: false,
				dataType: 'json',
				data: {
					action: "usces_admin_ajax",
					mode: "sales_sbps_card",
					order_id: $( "#order_id" ).val(),
					tracking_id: $( "#tracking_id" ).val(),
					member_id: $( "#member_id" ).val(),
					amount: amount,
					wc_nonce: $( "#wc_nonce" ).val()
				}
			}).done( function( retVal, dataType ) {
				$( "#settlement-response" ).html( retVal.result );
				if ( "OK" == retVal.status && 0 < retVal.acting_status.length ) {
					$( "#settlement-status" ).html( retVal.acting_status );
				}
				if ( $( "#refund-settlement" ).length ) {
					$( "#refund-settlement" ).prop( "disabled", true );
				}
				$( "#settlement-response-loading" ).html( "" );
			}).fail( function( jqXHR, textStatus, errorThrown ) {
				console.log( textStatus );
				console.log( jqXHR.status );
				console.log( errorThrown.message );
				$( "#settlement-response-loading" ).html( "" );
			});
			return false;
		},
							<?php
						endif;
						?>
		cancelSettlementCard : function() {
			$( "#settlement-response" ).html( "" );
			$( "#settlement-response-loading" ).html( '<img src="' + uscesL10n.USCES_PLUGIN_URL + '/images/loading.gif" />' );
			$.ajax({
				url: ajaxurl,
				type: "POST",
				cache: false,
				dataType: 'json',
				data: {
					action: "usces_admin_ajax",
					mode: "cancel_sbps_card",
					order_id: $( "#order_id" ).val(),
					tracking_id: $( "#tracking_id" ).val(),
					member_id: $( "#member_id" ).val(),
					wc_nonce: $( "#wc_nonce" ).val()
				}
			}).done( function( retVal, dataType ) {
				$( "#settlement-response" ).html( retVal.result );
				if ( "OK" == retVal.status && 0 < retVal.acting_status.length ) {
					$( "#settlement-status" ).html( retVal.acting_status );
				}
				if ( $( "#refund-settlement" ).length ) {
					$( "#refund-settlement" ).prop( "disabled", true );
				}
				$( "#settlement-response-loading" ).html( "" );
			}).fail( function( jqXHR, textStatus, errorThrown ) {
				console.log( textStatus );
				console.log( jqXHR.status );
				console.log( errorThrown.message );
				$( "#settlement-response-loading" ).html( "" );
			});
			return false;
		},
		refundSettlementCard : function( amount ) {
			$( "#settlement-response" ).html( "" );
			$( "#settlement-response-loading" ).html( '<img src="' + uscesL10n.USCES_PLUGIN_URL + '/images/loading.gif" />' );
			$.ajax({
				url: ajaxurl,
				type: "POST",
				cache: false,
				dataType: 'json',
				data: {
					action: "usces_admin_ajax",
					mode: "refund_sbps_card",
					order_id: $( "#order_id" ).val(),
					tracking_id: $( "#tracking_id" ).val(),
					member_id: $( "#member_id" ).val(),
					amount: amount,
					wc_nonce: $( "#wc_nonce" ).val()
				}
			}).done( function( retVal, dataType ) {
				$( "#settlement-response" ).html( retVal.result );
				if ( "OK" == retVal.status && 0 < retVal.acting_status.length ) {
					$( "#settlement-status" ).html( retVal.acting_status );
				}
				if ( $( "#refund-settlement" ).length ) {
					$( "#refund-settlement" ).prop( "disabled", true );
				}
				$( "#settlement-response-loading" ).html( "" );
			}).fail( function( jqXHR, textStatus, errorThrown ) {
				console.log( textStatus );
				console.log( jqXHR.status );
				console.log( errorThrown.message );
				$( "#settlement-response-loading" ).html( "" );
			});
			return false;
		},
						<?php
					/* PayPay */
					elseif ( 'acting_sbps_paypay' == $acting_flg ) :
						?>
		getSettlementInfoPayPay : function() {
			$( "#settlement-response" ).html( "" );
			$( "#settlement-response-loading" ).html( '<img src="' + uscesL10n.USCES_PLUGIN_URL + '/images/loading.gif" />' );
			var mode = ( "" != $( "#error" ).val() ) ? "error_sbps_paypay" : "get_sbps_paypay";
			$.ajax({
				url: ajaxurl,
				type: "POST",
				cache: false,
				dataType: 'json',
				data: {
					action: "usces_admin_ajax",
					mode: mode,
					order_id: $( "#order_id" ).val(),
					tracking_id: $( "#tracking_id" ).val(),
					member_id: $( "#member_id" ).val(),
					wc_nonce: $( "#wc_nonce" ).val()
				}
			}).done( function( retVal, dataType ) {
				$( "#settlement-response" ).html( retVal.result );
				if ( $( "#refund-settlement" ).length ) {
					$( "#refund-settlement" ).prop( "disabled", true );
				}
				if ( $( "#increase-settlement" ).length ) {
					$( "#increase-settlement" ).prop( "disabled", true );
				}
				$( "#settlement-response-loading" ).html( "" );
			}).fail( function( jqXHR, textStatus, errorThrown ) {
				console.log( textStatus );
				console.log( jqXHR.status );
				console.log( errorThrown.message );
				$( "#settlement-response-loading" ).html( "" );
			});
			return false;
		},
						<?php
						/* 指定売上 */
						if ( 'manual' == $acting_opts['paypay_sales'] ) :
							?>
		salesSettlementPayPay : function( amount ) {
			$( "#settlement-response" ).html( "" );
			$( "#settlement-response-loading" ).html( '<img src="' + uscesL10n.USCES_PLUGIN_URL + '/images/loading.gif" />' );
			$.ajax({
				url: ajaxurl,
				type: "POST",
				cache: false,
				dataType: 'json',
				data: {
					action: "usces_admin_ajax",
					mode: "sales_sbps_paypay",
					order_id: $( "#order_id" ).val(),
					tracking_id: $( "#tracking_id" ).val(),
					member_id: $( "#member_id" ).val(),
					amount: amount,
					wc_nonce: $( "#wc_nonce" ).val()
				}
			}).done( function( retVal, dataType ) {
				$( "#settlement-response" ).html( retVal.result );
				if ( "OK" == retVal.status && 0 < retVal.acting_status.length ) {
					$( "#settlement-status" ).html( retVal.acting_status );
				}
				if ( $( "#refund-settlement" ).length ) {
					$( "#refund-settlement" ).prop( "disabled", true );
				}
				if ( $( "#increase-settlement" ).length ) {
					$( "#increase-settlement" ).prop( "disabled", true );
				}
				$( "#settlement-response-loading" ).html( "" );
			}).fail( function( jqXHR, textStatus, errorThrown ) {
				console.log( textStatus );
				console.log( jqXHR.status );
				console.log( errorThrown.message );
				$( "#settlement-response-loading" ).html( "" );
			});
			return false;
		},
							<?php
							endif;
						?>
		cancelSettlementPayPay : function() {
			$( "#settlement-response" ).html( "" );
			$( "#settlement-response-loading" ).html( '<img src="' + uscesL10n.USCES_PLUGIN_URL + '/images/loading.gif" />' );
			$.ajax({
				url: ajaxurl,
				type: "POST",
				cache: false,
				dataType: 'json',
				data: {
					action: "usces_admin_ajax",
					mode: "cancel_sbps_paypay",
					order_id: $( "#order_id" ).val(),
					tracking_id: $( "#tracking_id" ).val(),
					member_id: $( "#member_id" ).val(),
					wc_nonce: $( "#wc_nonce" ).val()
				}
			}).done( function( retVal, dataType ) {
				$( "#settlement-response" ).html( retVal.result );
				if ( "OK" == retVal.status && 0 < retVal.acting_status.length ) {
					$( "#settlement-status" ).html( retVal.acting_status );
				}
				if ( $( "#refund-settlement" ).length ) {
					$( "#refund-settlement" ).prop( "disabled", true );
				}
				if ( $( "#increase-settlement" ).length ) {
					$( "#increase-settlement" ).prop( "disabled", true );
				}
				$( "#settlement-response-loading" ).html( "" );
			}).fail( function( jqXHR, textStatus, errorThrown ) {
				console.log( textStatus );
				console.log( jqXHR.status );
				console.log( errorThrown.message );
				$( "#settlement-response-loading" ).html( "" );
			});
			return false;
		},
		refundSettlementPayPay : function( amount ) {
			$( "#settlement-response" ).html( "" );
			$( "#settlement-response-loading" ).html( '<img src="' + uscesL10n.USCES_PLUGIN_URL + '/images/loading.gif" />' );
			$.ajax({
				url: ajaxurl,
				type: "POST",
				cache: false,
				dataType: 'json',
				data: {
					action: "usces_admin_ajax",
					mode: "refund_sbps_paypay",
					order_id: $( "#order_id" ).val(),
					tracking_id: $( "#tracking_id" ).val(),
					member_id: $( "#member_id" ).val(),
					amount: amount,
					wc_nonce: $( "#wc_nonce" ).val()
				}
			}).done( function( retVal, dataType ) {
				$( "#settlement-response" ).html( retVal.result );
				if ( "OK" == retVal.status && 0 < retVal.acting_status.length ) {
					$( "#settlement-status" ).html( retVal.acting_status );
				}
				if ( $( "#refund-settlement" ).length ) {
					$( "#refund-settlement" ).prop( "disabled", true );
				}
				if ( $( "#increase-settlement" ).length ) {
					$( "#increase-settlement" ).prop( "disabled", true );
				}
				$( "#settlement-response-loading" ).html( "" );
			}).fail( function( jqXHR, textStatus, errorThrown ) {
				console.log( textStatus );
				console.log( jqXHR.status );
				console.log( errorThrown.message );
				$( "#settlement-response-loading" ).html( "" );
			});
			return false;
		},
		increaseSettlementPayPay : function( amount ) {
			$( "#settlement-response" ).html( "" );
			$( "#settlement-response-loading" ).html( '<img src="' + uscesL10n.USCES_PLUGIN_URL + '/images/loading.gif" />' );
			$.ajax({
				url: ajaxurl,
				type: "POST",
				cache: false,
				dataType: 'json',
				data: {
					action: "usces_admin_ajax",
					mode: "increase_sbps_paypay",
					order_id: $( "#order_id" ).val(),
					tracking_id: $( "#tracking_id" ).val(),
					member_id: $( "#member_id" ).val(),
					amount: amount,
					wc_nonce: $( "#wc_nonce" ).val()
				}
			}).done( function( retVal, dataType ) {
				$( "#settlement-response" ).html( retVal.result );
				if ( ( "OK" == retVal.status || "AC" == retVal.status ) && 0 < retVal.acting_status.length ) {
					$( "#settlement-status" ).html( retVal.acting_status );
				}
				if ( $( "#refund-settlement" ).length ) {
					$( "#refund-settlement" ).prop( "disabled", true );
				}
				if ( $( "#increase-settlement" ).length ) {
					$( "#increase-settlement" ).prop( "disabled", true );
				}
				$( "#settlement-response-loading" ).html( "" );
			}).fail( function( jqXHR, textStatus, errorThrown ) {
				console.log( textStatus );
				console.log( jqXHR.status );
				console.log( errorThrown.message );
				$( "#settlement-response-loading" ).html( "" );
			});
			return false;
		},
						<?php
					endif;
					?>
	};

	$( "#settlement_dialog" ).dialog({
		bgiframe: true,
		autoOpen: false,
		height: "auto",
		width: 800,
		resizable: true,
		modal: true,
		buttons: {
			"<?php _e( 'Close' ); ?>": function() {
				$( this ).dialog( "close" );
			}
		},
		open: function() {
					<?php
					if ( 'acting_sbps_card' == $acting_flg ) :
						?>
			adminOrderEdit.getSettlementInfoCard();
						<?php
					elseif ( 'acting_sbps_paypay' == $acting_flg ) :
						?>
			adminOrderEdit.getSettlementInfoPayPay();
						<?php
					endif;
					?>
		},
		close: function() {
			<?php do_action( 'usces_action_sbps_settlement_dialog_close', $args ); ?>
		}
	});

	$( document ).on( "click", ".settlement-information", function() {
		var tracking_id = $( this ).attr( "data-tracking_id" );
		$( "#tracking_id" ).val( tracking_id );
		$( "#settlement_dialog" ).dialog( "option", "title", "<?php echo esc_js( $dialog_title ); ?>" );
		$( "#settlement_dialog" ).dialog( "open" );
	});

					<?php
					if ( 'acting_sbps_card' == $acting_flg ) :
						if ( 'manual' == $acting_opts['sales'] ) :
							?>
	$( document ).on( "click", "#sales-settlement", function() {
		var amount_original = parseInt( $( "#amount_original" ).val() ) || 0;
		var amount_change = parseInt( $( "#amount_change" ).val() ) || 0;
		if ( amount_change > amount_original ) {
			alert( "<?php _e( '与信金額を超える金額は売上計上できません。', 'usces' ); ?>" );
			return;
		}
		if ( amount_change < amount_original ) {
			if ( ! confirm( amount_change + "<?php _e( '円に減額して売上処理を実行します。よろしいですか？', 'usces' ); ?>" ) ) {
				return;
			}
		} else if ( ! confirm( "<?php _e( 'Are you sure you want to execute sales accounting processing?', 'usces' ); ?>" ) ) {
			return;
		}
		adminOrderEdit.salesSettlementCard( amount_change );
	});
							<?php
						endif;
						?>

	$( document ).on( "click", "#cancel-settlement", function() {
		if ( ! confirm( "<?php _e( 'Are you sure you want to cancellation processing?', 'usces' ); ?>" ) ) {
			return;
		}
		adminOrderEdit.cancelSettlementCard();
	});

	$( document ).on( "click", "#refund-settlement", function() {
		var amount_original = parseInt( $( "#amount_original" ).val() ) || 0;
		var amount_change = parseInt( $( "#amount_change" ).val() ) || 0;
		if ( amount_change == amount_original ) {
			return;
		}
		if ( amount_change > amount_original ) {
			alert( "<?php _e( '売上金額を超える金額は返金できません。', 'usces' ); ?>" );
			return;
		}
		if ( 0 == amount_change ) {
			if ( ! confirm( "<?php _e( 'Are you sure you want to cancellation processing?', 'usces' ); ?>" ) ) {
				return;
			}
			adminOrderEdit.cancelSettlementCard( amount_original );
		} else {
			var amount = amount_original - amount_change;
			if ( ! confirm( amount + "<?php _e( '円の返金処理を実行します。よろしいですか？', 'usces' ); ?>" ) ) {
				return;
			}
			adminOrderEdit.refundSettlementCard( amount );
		}
	});
						<?php
					elseif ( 'acting_sbps_paypay' == $acting_flg ) :
						if ( 'manual' == $acting_opts['paypay_sales'] ) :
							?>
	$( document ).on( "click", "#sales-settlement", function() {
		var amount_original = parseInt( $( "#amount_original" ).val() ) || 0;
		var amount_change = parseInt( $( "#amount_change" ).val() ) || 0;
		if ( amount_change > amount_original ) {
			alert( "<?php _e( '与信金額を超える金額は売上計上できません。', 'usces' ); ?>" );
			return;
		}
		if ( amount_change < amount_original ) {
			if ( ! confirm( amount_change + "<?php _e( '円に減額して売上処理を実行します。よろしいですか？', 'usces' ); ?>" ) ) {
				return;
			}
		} else if ( ! confirm( "<?php _e( 'Are you sure you want to execute sales accounting processing?', 'usces' ); ?>" ) ) {
			return;
		}
		adminOrderEdit.salesSettlementPayPay( amount_change );
	});
							<?php
						endif;
						?>

	$( document ).on( "click", "#cancel-settlement", function() {
		if ( ! confirm( "<?php _e( 'Are you sure you want to cancellation processing?', 'usces' ); ?>" ) ) {
			return;
		}
		adminOrderEdit.cancelSettlementPayPay();
	});

	$( document ).on( "click", "#refund-settlement", function() {
		var amount_original = parseInt( $( "#amount_original" ).val() ) || 0;
		var amount_change = parseInt( $( "#amount_change" ).val() ) || 0;
		if ( amount_change == amount_original ) {
			return;
		}
		if ( amount_change > amount_original ) {
			alert( "<?php _e( '返金できません。', 'usces' ); ?>" );
			return;
		}
		if ( 0 == amount_change ) {
			if ( ! confirm( "<?php _e( 'Are you sure you want to cancellation processing?', 'usces' ); ?>" ) ) {
				return;
			}
			adminOrderEdit.cancelSettlementPayPay( amount_original );
		} else {
			var amount = amount_original - amount_change;
			if ( ! confirm( amount + "<?php _e( '円の返金処理を実行します。よろしいですか？', 'usces' ); ?>" ) ) {
				return;
			}
			adminOrderEdit.refundSettlementPayPay( amount );
		}
	});

	$( document ).on( "click", "#increase-settlement", function() {
		var amount_original = parseInt( $( "#amount_original" ).val() ) || 0;
		var amount_change = parseInt( $( "#amount_change" ).val() ) || 0;
		if ( amount_change == amount_original ) {
			return;
		}
		if ( amount_change < amount_original ) {
			alert( "<?php _e( '増額できません。', 'usces' ); ?>" );
			return;
		}
		var amount = amount_change - amount_original;
		if ( ! confirm( amount + "<?php _e( '円の増額売上処理を実行します。よろしいですか？', 'usces' ); ?>" ) ) {
			return;
		}
		var amount = $( "#amount_change" ).val();
		adminOrderEdit.increaseSettlementPayPay( amount_change );
	});
						<?php
					endif;
					?>

	$( document ).on( "keydown", "#amount_change", function( e ) {
		var halfVal = $( this ).val().replace( /[！-～]/g,
			function( tmpStr ) {
				return String.fromCharCode( tmpStr.charCodeAt(0) - 0xFEE0 );
			}
		);
		$( this ).val( halfVal.replace( /[^0-9]/g, '' ) );
	});

	$( document ).on( "keyup", "#amount_change", function() {
		this.value = this.value.replace( /[^0-9]+/i, '' );
		this.value = Number( this.value ) || 0;
		var amount_original = Number( $( "#amount_original" ).val() ) || 0;
		if ( this.value > amount_original ) {
			$( "#refund-settlement" ).prop( "disabled", true );
					<?php
					if ( 'acting_sbps_paypay' == $acting_flg ) :
						?>
			$( "#increase-settlement" ).prop( "disabled", false );
						<?php
					endif;
					?>
		} else if ( this.value < amount_original ) {
			$( "#refund-settlement" ).prop( "disabled", false );
					<?php
					if ( 'acting_sbps_paypay' == $acting_flg ) :
						?>
			$( "#increase-settlement" ).prop( "disabled", true );
						<?php
					endif;
					?>
		} else {
			$( "#refund-settlement" ).prop( "disabled", true );
					<?php
					if ( 'acting_sbps_paypay' == $acting_flg ) :
						?>
			$( "#increase-settlement" ).prop( "disabled", true );
						<?php
					endif;
					?>
		}
	});

	$( document ).on( "blur", "#amount_change", function() {
		this.value = this.value.replace( /[^0-9]+/i, '' );
	});
});
</script>
					<?php
				endif;
				break;
		endswitch;
	}

	/**
	 * 決済オプション登録・更新
	 * usces_action_admin_settlement_update
	 */
	public function settlement_update() {
		global $usces;

		if ( 'sbps' != $_POST['acting'] ) {
			return;
		}

		$this->error_mes = '';
		$options = get_option( 'usces' );
		$payment_method = usces_get_system_option( 'usces_payment_method', 'settlement' );

		unset( $options['acting_settings']['sbps'] );
		$options['acting_settings']['sbps']['merchant_id'] = ( isset( $_POST['merchant_id'] ) ) ? trim( $_POST['merchant_id'] ) : '';
		$options['acting_settings']['sbps']['service_id'] = ( isset( $_POST['service_id'] ) ) ? trim( $_POST['service_id'] ) : '';
		$options['acting_settings']['sbps']['hash_key'] = ( isset( $_POST['hash_key'] ) ) ? trim( $_POST['hash_key'] ) : '';
		$options['acting_settings']['sbps']['ope'] = ( isset( $_POST['ope'] ) ) ? $_POST['ope'] : '';
		$options['acting_settings']['sbps']['card_activate'] = ( isset( $_POST['card_activate'] ) ) ? $_POST['card_activate'] : 'off';
		$options['acting_settings']['sbps']['3d_secure'] = ( isset( $_POST['3d_secure'] ) ) ? $_POST['3d_secure'] : 'off';
		$options['acting_settings']['sbps']['cust_manage'] = ( isset( $_POST['cust_manage'] ) ) ? $_POST['cust_manage'] : 'off';
		$options['acting_settings']['sbps']['sales'] = ( isset( $_POST['sales'] ) ) ? $_POST['sales'] : 'manual';
		$options['acting_settings']['sbps']['3des_key'] = ( isset( $_POST['3des_key'] ) ) ? trim( $_POST['3des_key'] ) : '';
		$options['acting_settings']['sbps']['3desinit_key'] = ( isset( $_POST['3desinit_key'] ) ) ? trim( $_POST['3desinit_key'] ) : '';
		$options['acting_settings']['sbps']['basic_id'] = ( isset( $_POST['basic_id'] ) ) ? trim( $_POST['basic_id'] ) : '';
		$options['acting_settings']['sbps']['basic_password'] = ( isset( $_POST['basic_password'] ) ) ? trim( $_POST['basic_password'] ) : '';
		$options['acting_settings']['sbps']['conv_activate'] = ( isset( $_POST['conv_activate'] ) ) ? $_POST['conv_activate'] : 'off';
		$options['acting_settings']['sbps']['payeasy_activate'] = ( isset( $_POST['payeasy_activate'] ) ) ? $_POST['payeasy_activate'] : 'off';
		$options['acting_settings']['sbps']['wallet_yahoowallet'] = ( isset( $_POST['wallet_yahoowallet'] ) ) ? $_POST['wallet_yahoowallet'] : 'off';
		$options['acting_settings']['sbps']['wallet_rakuten'] = ( isset( $_POST['wallet_rakuten'] ) ) ? $_POST['wallet_rakuten'] : 'off';
		$options['acting_settings']['sbps']['wallet_paypal'] = ( isset( $_POST['wallet_paypal'] ) ) ? $_POST['wallet_paypal'] : 'off';
		$options['acting_settings']['sbps']['wallet_netmile'] = 'off';
		$options['acting_settings']['sbps']['wallet_alipay'] = ( isset( $_POST['wallet_alipay'] ) ) ? $_POST['wallet_alipay'] : 'off';
		$options['acting_settings']['sbps']['wallet_activate'] = ( isset( $_POST['wallet_activate'] ) ) ? $_POST['wallet_activate'] : 'off';
		$options['acting_settings']['sbps']['mobile_docomo'] = ( isset( $_POST['mobile_docomo'] ) ) ? $_POST['mobile_docomo'] : 'off';
		$options['acting_settings']['sbps']['mobile_auone'] = ( isset( $_POST['mobile_auone'] ) ) ? $_POST['mobile_auone'] : 'off';
		$options['acting_settings']['sbps']['mobile_mysoftbank'] = 'off';
		$options['acting_settings']['sbps']['mobile_softbank2'] = ( isset( $_POST['mobile_softbank2'] ) ) ? $_POST['mobile_softbank2'] : 'off';
		$options['acting_settings']['sbps']['mobile_activate'] = ( isset( $_POST['mobile_activate'] ) ) ? $_POST['mobile_activate'] : 'off';
		$options['acting_settings']['sbps']['paypay_activate'] = ( isset( $_POST['paypay_activate'] ) ) ? $_POST['paypay_activate'] : 'off';
		$options['acting_settings']['sbps']['paypay_sales'] = ( isset( $_POST['paypay_sales'] ) ) ? $_POST['paypay_sales'] : 'manual';

		if ( ( 'on' == $options['acting_settings']['sbps']['card_activate'] || 'token' == $options['acting_settings']['sbps']['card_activate'] ) ||
			'on' == $options['acting_settings']['sbps']['conv_activate'] ||
			'on' == $options['acting_settings']['sbps']['payeasy_activate'] ||
			'on' == $options['acting_settings']['sbps']['wallet_activate'] ||
			'on' == $options['acting_settings']['sbps']['mobile_activate'] ||
			'on' == $options['acting_settings']['sbps']['paypay_activate'] ) {
			$unavailable_activate = false;
			foreach ( $payment_method as $settlement => $payment ) {
				if ( in_array( $settlement, $this->unavailable_method ) && 'activate' == $payment['use'] ) {
					$unavailable_activate = true;
					break;
				}
			}
			if ( $unavailable_activate ) {
				$this->error_mes .= __( '* Settlement that can not be used together is activated.', 'usces' ) . '<br />';
			} else {
				if ( WCUtils::is_blank( $_POST['merchant_id'] ) ) {
					$this->error_mes .= '※マーチャントID を入力してください<br />';
				}
				if ( WCUtils::is_blank( $_POST['service_id'] ) ) {
					$this->error_mes .= '※サービスID を入力してください<br />';
				}
				if ( WCUtils::is_blank( $_POST['hash_key'] ) ) {
					$this->error_mes .= '※ハッシュキーを入力してください<br />';
				}
				if ( ( 'on' == $options['acting_settings']['sbps']['card_activate'] || 'token' == $options['acting_settings']['sbps']['card_activate'] ) ||
					'on' == $options['acting_settings']['sbps']['paypay_activate'] ) {
					if ( WCUtils::is_blank( $_POST['3des_key'] ) ) {
						$this->error_mes .= '※3DES 暗号化キーを入力してください<br />';
					}
					if ( WCUtils::is_blank( $_POST['3desinit_key'] ) ) {
						$this->error_mes .= '※3DES 初期化キーを入力してください<br />';
					}
				}
				if ( 'token' == $options['acting_settings']['sbps']['card_activate'] ) {
					if ( WCUtils::is_blank( $_POST['basic_id'] ) ) {
						$this->error_mes .= '※Basic認証ID を入力してください<br />';
					}
					if ( WCUtils::is_blank( $_POST['basic_password'] ) ) {
						$this->error_mes .= '※Basic認証 Password を入力してください<br />';
					}
				}
			}
		}

		if ( '' == $this->error_mes ) {
			$usces->action_status = 'success';
			$usces->action_message = __( 'Options are updated.', 'usces' );
			$toactive = array();
			if ( 'on' == $options['acting_settings']['sbps']['card_activate'] || 'token' == $options['acting_settings']['sbps']['card_activate'] ) {
				$usces->payment_structure[ $this->acting_flg_card ] = 'カード決済（SBPS）';
				foreach ( $payment_method as $settlement => $payment ) {
					if ( $this->acting_flg_card == $settlement && 'deactivate' == $payment['use'] ) {
						$toactive[] = $payment['name'];
					}
				}
			} else {
				unset( $usces->payment_structure[ $this->acting_flg_card ] );
			}
			if ( 'on' == $options['acting_settings']['sbps']['conv_activate'] ) {
				$usces->payment_structure[ $this->acting_flg_conv ] = 'コンビニ決済（SBPS）';
				foreach ( $payment_method as $settlement => $payment ) {
					if ( $this->acting_flg_conv == $settlement && 'deactivate' == $payment['use'] ) {
						$toactive[] = $payment['name'];
					}
				}
			} else {
				unset( $usces->payment_structure[ $this->acting_flg_conv ] );
			}
			if ( 'on' == $options['acting_settings']['sbps']['payeasy_activate'] ) {
				$usces->payment_structure[ $this->acting_flg_payeasy ] = 'ペイジー決済（SBPS）';
				foreach ( $payment_method as $settlement => $payment ) {
					if ( $this->acting_flg_payeasy == $settlement && 'deactivate' == $payment['use'] ) {
						$toactive[] = $payment['name'];
					}
				}
			} else {
				unset( $usces->payment_structure[ $this->acting_flg_payeasy ] );
			}
			if ( 'on' == $options['acting_settings']['sbps']['wallet_yahoowallet'] ||
				'on' == $options['acting_settings']['sbps']['wallet_rakuten'] ||
				'on' == $options['acting_settings']['sbps']['wallet_paypal'] ||
				'on' == $options['acting_settings']['sbps']['wallet_alipay'] ) {
				$options['acting_settings']['sbps']['wallet_activate'] = 'on';
			} else {
				$options['acting_settings']['sbps']['wallet_activate'] = 'off';
			}
			if ( 'on' == $options['acting_settings']['sbps']['wallet_activate'] ) {
				$usces->payment_structure[ $this->acting_flg_wallet ] = 'ウォレット決済（SBPS）';
				foreach ( $payment_method as $settlement => $payment ) {
					if ( $this->acting_flg_wallet == $settlement && 'deactivate' == $payment['use'] ) {
						$toactive[] = $payment['name'];
					}
				}
			} else {
				unset( $usces->payment_structure[ $this->acting_flg_wallet ] );
			}
			if ( 'on' == $options['acting_settings']['sbps']['mobile_docomo'] ||
				'on' == $options['acting_settings']['sbps']['mobile_auone'] ||
				'on' == $options['acting_settings']['sbps']['mobile_softbank2'] ) {
				$options['acting_settings']['sbps']['mobile_activate'] = 'on';
			} else {
				$options['acting_settings']['sbps']['mobile_activate'] = 'off';
			}
			if ( 'on' == $options['acting_settings']['sbps']['mobile_activate'] ) {
				$usces->payment_structure[ $this->acting_flg_mobile ] = 'キャリア決済（SBPS）';
				foreach ( $payment_method as $settlement => $payment ) {
					if ( $this->acting_flg_mobile == $settlement && 'deactivate' == $payment['use'] ) {
						$toactive[] = $payment['name'];
					}
				}
			} else {
				unset( $usces->payment_structure[ $this->acting_flg_mobile ] );
			}
			if ( 'on' == $options['acting_settings']['sbps']['paypay_activate'] ) {
				$usces->payment_structure[ $this->acting_flg_paypay ] = 'PayPay オンライン決済（SBPS）';
				foreach ( $payment_method as $settlement => $payment ) {
					if ( $this->acting_flg_paypay == $settlement && 'deactivate' == $payment['use'] ) {
						$toactive[] = $payment['name'];
					}
				}
			} else {
				unset( $usces->payment_structure[ $this->acting_flg_paypay ] );
			}
			if ( ( 'on' == $options['acting_settings']['sbps']['card_activate'] || 'token' == $options['acting_settings']['sbps']['card_activate'] ) ||
				'on' == $options['acting_settings']['sbps']['conv_activate'] ||
				'on' == $options['acting_settings']['sbps']['payeasy_activate'] ||
				'on' == $options['acting_settings']['sbps']['wallet_activate'] ||
				'on' == $options['acting_settings']['sbps']['mobile_activate'] ||
				'on' == $options['acting_settings']['sbps']['paypay_activate'] ) {
				$options['acting_settings']['sbps']['activate'] = 'on';
				$options['acting_settings']['sbps']['send_url'] = 'https://fep.sps-system.com/f01/FepBuyInfoReceive.do';
				$options['acting_settings']['sbps']['send_url_check'] = 'https://stbfep.sps-system.com/Extra/BuyRequestAction.do';
				$options['acting_settings']['sbps']['send_url_test'] = 'https://stbfep.sps-system.com/f01/FepBuyInfoReceive.do';
				$options['acting_settings']['sbps']['token_url'] = 'https://token.sps-system.com/sbpstoken/com_sbps_system_token.js';
				$options['acting_settings']['sbps']['token_url_test'] = 'https://stbtoken.sps-system.com/sbpstoken/com_sbps_system_token.js';
				$options['acting_settings']['sbps']['api_url'] = 'https://fep.sps-system.com/api/xmlapi.do';
				$options['acting_settings']['sbps']['api_url_test'] = 'https://stbfep.sps-system.com/api/xmlapi.do';
				usces_admin_orderlist_show_wc_trans_id();
				if ( 0 < count( $toactive ) ) {
					$usces->action_message .= __( "Please update the payment method to \"Activate\". <a href=\"admin.php?page=usces_initial#payment_method_setting\">General Setting > Payment Methods</a>", 'usces' );
				}
			} else {
				$options['acting_settings']['sbps']['activate'] = 'off';
				unset( $usces->payment_structure[ $this->acting_flg_card ] );
				unset( $usces->payment_structure[ $this->acting_flg_conv ] );
				unset( $usces->payment_structure[ $this->acting_flg_payeasy ] );
				unset( $usces->payment_structure[ $this->acting_flg_wallet ] );
				unset( $usces->payment_structure[ $this->acting_flg_mobile ] );
				unset( $usces->payment_structure[ $this->acting_flg_paypay ] );
			}
			$deactivate = array();
			foreach ( $payment_method as $settlement => $payment ) {
				if ( ! array_key_exists( $settlement, $usces->payment_structure ) ) {
					if ( 'deactivate' != $payment['use'] ) {
						$payment['use'] = 'deactivate';
						$deactivate[] = $payment['name'];
						usces_update_system_option( 'usces_payment_method', $payment['id'], $payment );
					}
				}
			}
			if ( 0 < count( $deactivate ) ) {
				$deactivate_message = sprintf( __( "\"Deactivate\" %s of payment method.", 'usces' ), implode( ',', $deactivate ) );
				$usces->action_message .= $deactivate_message;
			}
		} else {
			$usces->action_status = 'error';
			$usces->action_message = __( 'Data have deficiency.', 'usces' );
			$options['acting_settings']['sbps']['activate'] = 'off';
			unset( $usces->payment_structure[ $this->acting_flg_card ] );
			unset( $usces->payment_structure[ $this->acting_flg_conv ] );
			unset( $usces->payment_structure[ $this->acting_flg_payeasy ] );
			unset( $usces->payment_structure[ $this->acting_flg_wallet ] );
			unset( $usces->payment_structure[ $this->acting_flg_mobile ] );
			unset( $usces->payment_structure[ $this->acting_flg_paypay ] );
			$deactivate = array();
			foreach ( $payment_method as $settlement => $payment ) {
				if ( in_array( $settlement, $this->pay_method ) ) {
					if ( 'deactivate' != $payment['use'] ) {
						$payment['use'] = 'deactivate';
						$deactivate[] = $payment['name'];
						usces_update_system_option( 'usces_payment_method', $payment['id'], $payment );
					}
				}
			}
			if ( 0 < count( $deactivate ) ) {
				$deactivate_message = sprintf( __( "\"Deactivate\" %s of payment method.", 'usces' ), implode( ',', $deactivate ) );
				$usces->action_message .= $deactivate_message . __( "Please complete the setup and update the payment method to \"Activate\".", 'usces' );
			}
		}
		ksort( $usces->payment_structure );
		update_option( 'usces', $options );
		update_option( 'usces_payment_structure', $usces->payment_structure );
	}

	/**
	 * クレジット決済設定画面フォーム
	 * usces_action_settlement_tab_body
	 */
	public function settlement_tab_body() {

		$acting_opts = $this->get_acting_settings();
		$settlement_selected = get_option( 'usces_settlement_selected' );
		if ( in_array( 'sbps', (array) $settlement_selected ) ) :
			?>
	<div id="uscestabs_sbps">
	<div class="settlement_service"><span class="service_title"><?php esc_html_e( $this->acting_formal_name ); ?></span></div>
			<?php
			if ( isset( $_POST['acting'] ) && 'sbps' == $_POST['acting'] ) :
				if ( '' != $this->error_mes ) :
					?>
		<div class="error_message"><?php echo $this->error_mes; ?></div>
					<?php
				elseif ( isset( $acting_opts['activate'] ) && 'on' == $acting_opts['activate'] ) :
					?>
		<div class="message"><?php _e( 'Test thoroughly before use.', 'usces' ); ?></div>
					<?php
				endif;
			endif;
			?>
	<form action="" method="post" name="sbps_form" id="sbps_form">
		<table class="settle_table">
			<tr>
				<th><a class="explanation-label" id="label_ex_merchant_id_sbps">マーチャントID</a></th>
				<td><input name="merchant_id" type="text" id="merchant_id_sbps" value="<?php echo esc_html( isset( $acting_opts['merchant_id'] ) ? $acting_opts['merchant_id'] : '' ); ?>" class="regular-text" maxlength="5" /></td>
			</tr>
			<tr id="ex_merchant_id_sbps" class="explanation"><td colspan="2">契約時にSBペイメントサービスから発行されるマーチャントID（半角数字）</td></tr>
			<tr>
				<th><a class="explanation-label" id="label_ex_service_id_sbps">サービスID</a></th>
				<td><input name="service_id" type="text" id="service_id_sbps" value="<?php echo esc_html( isset( $acting_opts['service_id'] ) ? $acting_opts['service_id'] : '' ); ?>" class="regular-text" maxlength="3" /></td>
			</tr>
			<tr id="ex_service_id_sbps" class="explanation"><td colspan="2">契約時にSBペイメントサービスから発行されるサービスID（半角数字）</td></tr>
			<tr>
				<th><a class="explanation-label" id="label_ex_hash_key_sbps">ハッシュキー</a></th>
				<td><input name="hash_key" type="text" id="hash_key_sbps" value="<?php echo esc_html( isset( $acting_opts['hash_key'] ) ? $acting_opts['hash_key'] : '' ); ?>" class="regular-text" maxlength="40" /></td>
			</tr>
			<tr id="ex_hash_key_sbps" class="explanation"><td colspan="2">契約時にSBペイメントサービスから発行されるハッシュキー（半角英数）</td></tr>
			<tr>
				<th><a class="explanation-label" id="label_ex_3des_key_sbps">3DES<br />暗号化キー</a></th>
				<td><input name="3des_key" type="text" id="3des_key_sbps" value="<?php echo esc_html( isset( $acting_opts['3des_key'] ) ? $acting_opts['3des_key'] : '' ); ?>" class="regular-text" maxlength="40" /></td>
			</tr>
			<tr id="ex_3des_key_sbps" class="explanation card_token_sbps"><td colspan="2">契約時にSBペイメントサービスから発行される 3DES 暗号化キー（半角英数）</td></tr>
			<tr>
				<th><a class="explanation-label" id="label_ex_3desinit_key_sbps">3DES<br />初期化キー</a></th>
				<td><input name="3desinit_key" type="text" id="3desinit_key_sbps" value="<?php echo esc_html( isset( $acting_opts['3desinit_key'] ) ? $acting_opts['3desinit_key'] : '' ); ?>" class="regular-text" maxlength="40" /></td>
			</tr>
			<tr id="ex_3desinit_key_sbps" class="explanation card_token_sbps"><td colspan="2">契約時にSBペイメントサービスから発行される 3DES 初期化キー（半角英数）</td></tr>
			<tr>
				<th><a class="explanation-label" id="label_ex_ope_sbps"><?php _e( 'Operation Environment', 'usces' ); ?></a></th>
				<td><label><input name="ope" type="radio" id="ope_sbps_1" value="check"<?php if ( isset( $acting_opts['ope'] ) && $acting_opts['ope'] == 'check' ) echo ' checked="checked"'; ?> /><span>接続支援サイト</span></label><br />
					<label><input name="ope" type="radio" id="ope_sbps_2" value="test"<?php if ( isset( $acting_opts['ope'] ) && $acting_opts['ope'] == 'test' ) echo ' checked="checked"'; ?> /><span>テスト環境</span></label><br />
					<label><input name="ope" type="radio" id="ope_sbps_3" value="public"<?php if ( isset( $acting_opts['ope'] ) && $acting_opts['ope'] == 'public' ) echo ' checked="checked"'; ?> /><span>本番環境</span></label>
				</td>
			</tr>
			<tr id="ex_ope_sbps" class="explanation"><td colspan="2"><?php _e( 'Switch the operating environment.', 'usces' ); ?></td></tr>
		</table>
		<table class="settle_table">
			<tr>
				<th>クレジットカード決済</th>
				<td><label><input name="card_activate" type="radio" class="card_activate_sbps" id="card_activate_sbps_1" value="on"<?php if ( isset( $acting_opts['card_activate'] ) && $acting_opts['card_activate'] == 'on' ) echo ' checked="checked"'; ?> /><span>リンク型で利用する</span></label><br />
					<label><input name="card_activate" type="radio" class="card_activate_sbps" id="card_activate_sbps_2" value="token"<?php if ( isset( $acting_opts['card_activate'] ) && $acting_opts['card_activate'] == 'token' ) echo ' checked="checked"'; ?> /><span>API 型で利用する</span></label><br />
					<label><input name="card_activate" type="radio" class="card_activate_sbps" id="card_activate_sbps_0" value="off"<?php if ( isset( $acting_opts['card_activate'] ) && $acting_opts['card_activate'] == 'off' ) echo ' checked="checked"'; ?> /><span><?php _e( 'Do not Use', 'usces' ); ?></span></label>
				</td>
			</tr>
			<tr class="card_token_sbps">
				<th><a class="explanation-label" id="label_ex_cust_manage_sbps">クレジットカード情報保存</a></th>
				<td><label><input name="cust_manage" type="radio" id="cust_manage_sbps_1" value="on"<?php if ( $acting_opts['cust_manage'] == 'on' ) echo ' checked="checked"'; ?> /><span>保存する</span></label><br />
					<label><input name="cust_manage" type="radio" id="cust_manage_sbps_2" value="choice"<?php if ( $acting_opts['cust_manage'] == 'choice' ) echo ' checked="checked"'; ?> /><span>会員が選択して保存する</span></label><br />
					<label><input name="cust_manage" type="radio" id="cust_manage_sbps_0" value="off"<?php if ( $acting_opts['cust_manage'] == 'off' ) echo ' checked="checked"'; ?> /><span>保存しない</span></label>
				</td>
			</tr>
			<tr id="ex_cust_manage_sbps" class="explanation card_token_sbps"><td colspan="2">クレジットカード情報お預かりサービスを利用して、会員のカード情報をSBペイメントサービスに保存します。</td></tr>
			<tr class="card_link_token_sbps">
				<th><a class="explanation-label" id="label_ex_sales_sbps">売上方式</a></th>
				<td><label><input name="sales" type="radio" id="sales_sbps_manual" value="manual"<?php if ( $acting_opts['sales'] == 'manual' ) echo ' checked="checked"'; ?> /><span>指定売上（仮売上）</span></label><br />
					<label><input name="sales" type="radio" id="sales_sbps_auto" value="auto"<?php if ( $acting_opts['sales'] == 'auto' ) echo ' checked="checked"'; ?> /><span>自動売上（実売上）</span></label>
				</td>
			</tr>
			<tr id="ex_sales_sbps" class="explanation card_link_token_sbps"><td colspan="2">指定売上の場合は、決済時には与信のみ行い、Welcart の管理画面から手動で売上処理を行います。自動売上の場合は、決済時に即時売上計上されます。</td></tr>
			<tr class="card_token_sbps">
				<th><a class="explanation-label" id="label_ex_basic_id_sbps">Basic認証ID</a></th>
				<td><input name="basic_id" type="text" id="basic_id_sbps" value="<?php echo esc_html( isset( $acting_opts['basic_id'] ) ? $acting_opts['basic_id'] : '' ); ?>" class="regular-text" maxlength="40" /></td>
			</tr>
			<tr id="ex_basic_id_sbps" class="explanation card_token_sbps"><td colspan="2">契約時にSBペイメントサービスから発行される Basic認証ID（半角数字）</td></tr>
			<tr class="card_token_sbps">
				<th><a class="explanation-label" id="label_ex_basic_password_sbps">Basic認証Password</a></th>
				<td><input name="basic_password" type="text" id="basic_password_sbps" value="<?php echo esc_html( isset( $acting_opts['basic_password'] ) ? $acting_opts['basic_password'] : '' ); ?>" class="regular-text" maxlength="40" /></td>
			</tr>
			<tr id="ex_basic_password_sbps" class="explanation card_token_sbps"><td colspan="2">契約時にSBペイメントサービスから発行される Basic認証 Password（半角英数）</td></tr>
			<tr class="card_link_sbps">
				<th>3Dセキュア</th>
				<td><label><input name="3d_secure" type="radio" id="3d_secure_sbps_1" value="on"<?php if ( isset( $acting_opts['3d_secure'] ) && $acting_opts['3d_secure'] == 'on' ) echo ' checked="checked"'; ?> /><span><?php _e( 'Use', 'usces' ); ?></span></label><br />
					<label><input name="3d_secure" type="radio" id="3d_secure_sbps_2" value="off"<?php if ( isset( $acting_opts['3d_secure'] ) && $acting_opts['3d_secure'] == 'off' ) echo ' checked="checked"'; ?> /><span><?php _e( 'Do not Use', 'usces' ); ?></span></label>
				</td>
			</tr>
		</table>
		<table class="settle_table">
			<tr>
				<th>コンビニ決済</th>
				<td><label><input name="conv_activate" type="radio" id="conv_activate_sbps_1" value="on"<?php if ( isset( $acting_opts['conv_activate'] ) && $acting_opts['conv_activate'] == 'on' ) echo ' checked="checked"'; ?> /><span><?php _e( 'Use', 'usces' ); ?></span></label><br />
					<label><input name="conv_activate" type="radio" id="conv_activate_sbps_2" value="off"<?php if ( isset( $acting_opts['conv_activate'] ) && $acting_opts['conv_activate'] == 'off' ) echo ' checked="checked"'; ?> /><span><?php _e( 'Do not Use', 'usces' ); ?></span></label>
				</td>
			</tr>
		</table>
		<table class="settle_table">
			<tr>
				<th>Pay-easy（ペイジー）決済</th>
				<td><label><input name="payeasy_activate" type="radio" id="payeasy_activate_sbps_1" value="on"<?php if ( isset( $acting_opts['payeasy_activate'] ) && $acting_opts['payeasy_activate'] == 'on' ) echo ' checked="checked"'; ?> /><span><?php _e( 'Use', 'usces' ); ?></span></label><br />
					<label><input name="payeasy_activate" type="radio" id="payeasy_activate_sbps_2" value="off"<?php if ( isset( $acting_opts['payeasy_activate'] ) && $acting_opts['payeasy_activate'] == 'off' ) echo ' checked="checked"'; ?> /><span><?php _e( 'Do not Use', 'usces' ); ?></span></label>
				</td>
			</tr>
		</table>
		<table class="settle_table">
			<tr>
				<th>Yahoo! ウォレット決済</th>
				<td><label><input name="wallet_yahoowallet" type="radio" id="wallet_yahoowallet_sbps_1" value="on"<?php if ( isset( $acting_opts['wallet_yahoowallet'] ) && $acting_opts['wallet_yahoowallet'] == 'on' ) echo ' checked="checked"'; ?> /><span><?php _e( 'Use', 'usces' ); ?></span></label><br />
					<label><input name="wallet_yahoowallet" type="radio" id="wallet_yahoowallet_sbps_2" value="off"<?php if ( isset( $acting_opts['wallet_yahoowallet'] ) && $acting_opts['wallet_yahoowallet'] == 'off' ) echo ' checked="checked"'; ?> /><span><?php _e( 'Do not Use', 'usces' ); ?></span></label>
				</td>
			</tr>
			<tr>
				<th>楽天ペイ（オンライン決済）</th>
				<td><label><input name="wallet_rakuten" type="radio" id="wallet_rakuten_sbps_1" value="on"<?php if ( isset( $acting_opts['wallet_rakuten'] ) && $acting_opts['wallet_rakuten'] == 'on' ) echo ' checked="checked"'; ?> /><span><?php _e( 'Use', 'usces' ); ?></span></label><br />
					<label><input name="wallet_rakuten" type="radio" id="wallet_rakuten_sbps_2" value="off"<?php if ( isset( $acting_opts['wallet_rakuten'] ) && $acting_opts['wallet_rakuten'] == 'off' ) echo ' checked="checked"'; ?> /><span><?php _e( 'Do not Use', 'usces' ); ?></span></label>
				</td>
			</tr>
			<tr>
				<th>PayPal 決済</th>
				<td><label><input name="wallet_paypal" type="radio" id="wallet_paypal_sbps_1" value="on"<?php if ( isset( $acting_opts['wallet_paypal'] ) && $acting_opts['wallet_paypal'] == 'on' ) echo ' checked="checked"'; ?> /><span><?php _e( 'Use', 'usces' ); ?></span></label><br />
					<label><input name="wallet_paypal" type="radio" id="wallet_paypal_sbps_2" value="off"<?php if ( isset( $acting_opts['wallet_paypal'] ) && $acting_opts['wallet_paypal'] == 'off' ) echo ' checked="checked"'; ?> /><span><?php _e( 'Do not Use', 'usces' ); ?></span></label>
				</td>
			</tr>
			<tr>
				<th>Alipay 国際決済</th>
				<td><label><input name="wallet_alipay" type="radio" id="wallet_alipay_sbps_1" value="on"<?php if ( isset( $acting_opts['wallet_alipay'] ) && $acting_opts['wallet_alipay'] == 'on' ) echo ' checked="checked"'; ?> /><span><?php _e( 'Use', 'usces' ); ?></span></label><br />
					<label><input name="wallet_alipay" type="radio" id="wallet_alipay_sbps_2" value="off"<?php if ( isset( $acting_opts['wallet_alipay'] ) && $acting_opts['wallet_alipay'] == 'off' ) echo ' checked="checked"'; ?> /><span><?php _e( 'Do not Use', 'usces' ); ?></span></label>
				</td>
			</tr>
		</table>
		<table class="settle_table">
			<tr>
				<th>ドコモ払い</th>
				<td><label><input name="mobile_docomo" type="radio" id="mobile_docomo_sbps_1" value="on"<?php if ( isset( $acting_opts['mobile_docomo'] ) && $acting_opts['mobile_docomo'] == 'on' ) echo ' checked="checked"'; ?> /><span><?php _e( 'Use', 'usces' ); ?></span></label><br />
					<label><input name="mobile_docomo" type="radio" id="mobile_docomo_sbps_2" value="off"<?php if ( isset( $acting_opts['mobile_docomo'] ) && $acting_opts['mobile_docomo'] == 'off' ) echo ' checked="checked"'; ?> /><span><?php _e( 'Do not Use', 'usces' ); ?></span></label>
				</td>
			</tr>
			<tr>
				<th>au かんたん決済</th>
				<td><label><input name="mobile_auone" type="radio" id="mobile_auone_sbps_1" value="on"<?php if ( isset( $acting_opts['mobile_auone'] ) && $acting_opts['mobile_auone'] == 'on' ) echo ' checked="checked"'; ?> /><span><?php _e( 'Use', 'usces' ); ?></span></label><br />
					<label><input name="mobile_auone" type="radio" id="mobile_auone_sbps_2" value="off"<?php if ( isset( $acting_opts['mobile_auone'] ) && $acting_opts['mobile_auone'] == 'off' ) echo ' checked="checked"'; ?> /><span><?php _e( 'Do not Use', 'usces' ); ?></span></label>
				</td>
			</tr>
			<tr>
				<th>ソフトバンク<br />まとめて支払い</th>
				<td><label><input name="mobile_softbank2" type="radio" id="mobile_softbank2_sbps_1" value="on"<?php if ( isset( $acting_opts['mobile_softbank2'] ) && $acting_opts['mobile_softbank2'] == 'on' ) echo ' checked="checked"'; ?> /><span><?php _e( 'Use', 'usces' ); ?></span></label><br />
					<label><input name="mobile_softbank2" type="radio" id="mobile_softbank2_sbps_2" value="off"<?php if ( isset( $acting_opts['mobile_softbank2'] ) && $acting_opts['mobile_softbank2'] == 'off' ) echo ' checked="checked"'; ?> /><span><?php _e( 'Do not Use', 'usces' ); ?></span></label>
				</td>
			</tr>
		</table>
		<table class="settle_table">
			<tr>
				<th>PayPay オンライン決済</th>
				<td><label><input name="paypay_activate" type="radio" class="paypay_activate_sbps" id="paypay_activate_sbps_1" value="on"<?php if ( isset( $acting_opts['paypay_activate'] ) && $acting_opts['paypay_activate'] == 'on' ) echo ' checked="checked"'; ?> /><span><?php _e( 'Use', 'usces' ); ?></span></label><br />
					<label><input name="paypay_activate" type="radio" class="paypay_activate_sbps" id="paypay_activate_sbps_2" value="off"<?php if ( isset( $acting_opts['paypay_activate'] ) && $acting_opts['paypay_activate'] == 'off' ) echo ' checked="checked"'; ?> /><span><?php _e( 'Do not Use', 'usces' ); ?></span></label>
				</td>
			</tr>
			<tr class="paypay_sbps">
				<th><a class="explanation-label" id="label_ex_paypay_sales_sbps">売上方式</a></th>
				<td><label><input name="paypay_sales" type="radio" id="paypay_sales_sbps_manual" value="manual"<?php if ( $acting_opts['paypay_sales'] == 'manual' ) echo ' checked="checked"'; ?> /><span>指定売上（仮売上）</span></label><br />
					<label><input name="paypay_sales" type="radio" id="paypay_sales_sbps_auto" value="auto"<?php if ( $acting_opts['paypay_sales'] == 'auto' ) echo ' checked="checked"'; ?> /><span>自動売上（実売上）</span></label>
				</td>
			</tr>
			<tr id="ex_paypay_sales_sbps" class="explanation paypay_sbps"><td colspan="2">指定売上の場合は、決済時には与信のみ行い、Welcart の管理画面から手動で売上処理を行います。自動売上の場合は、決済時に即時売上計上されます。</td></tr>
		</table>
		<input name="acting" type="hidden" value="sbps" />
		<input name="usces_option_update" type="submit" class="button button-primary" value="SBペイメントサービスの設定を更新する" />
			<?php
			wp_nonce_field( 'admin_settlement', 'wc_nonce' );
			?>
	</form>
	<div class="settle_exp">
		<p><strong>SBペイメントサービス</strong></p>
		<a href="https://www.welcart.com/wc-settlement/sbps_guide/" target="_blank">SBペイメントサービスの詳細はこちら 》</a>
		<p></p>
		<p>クレジットカード決済では、「API型（トークン決済方式）」と「リンク型」が選択できます。</p>
		<p>「API型」は、決済会社のページへは遷移せず、Welcart のページのみで決済まで完結します。デザインの統一性が保て、スムーズなチェックアウトが可能です。ただし、カード番号を扱いますので専用SSLが必須となります。入力されたカード番号はSBペイメントサービスのシステムに送信されますので、Welcart に保存することはありません。<br />
		「リンク型」は、決済会社のページへ遷移してカード情報を入力します。<br />
		クレジットカード決済以外の決済サービスでは、全て「リンク型」になります。</p>
		<p>尚、本番環境では、正規SSL証明書のみでのSSL通信となりますのでご注意ください。</p>
	</div>
	</div><!-- uscestabs_sbps -->
			<?php
		endif;
	}

	/**
	 * 通知処理
	 * usces_after_cart_instant
	 */
	public function acting_notice() {
		global $usces;

		if ( isset( $_SERVER['REMOTE_ADDR'] ) && '61.215.213.47' == $_SERVER['REMOTE_ADDR'] ) {
			$post_data = file_get_contents( 'php://input' );
			$request_data = $this->xml2assoc( $post_data );
			// usces_log( print_r( $request_data, true ), "acting_notice.log" );
			if ( isset( $request_data['@attributes']['id'] ) && isset( $request_data['merchant_id'] ) && isset( $request_data['service_id'] ) && isset( $request_data['sps_transaction_id'] ) && isset( $request_data['tracking_id'] ) && isset( $request_data['pay_option_manage'] ) ) {
				$acting_opts = $this->get_acting_settings();
				if ( $acting_opts['merchant_id'] == $request_data['merchant_id'] && $acting_opts['service_id'] == $request_data['service_id'] ) {
					$latest_log = $this->get_acting_latest_log( 0, $request_data['tracking_id'] );
					if ( $latest_log && isset( $latest_log['status'] ) && 'pending' == $latest_log['status'] && isset( $latest_log['order_id'] ) ) {
						$attributes_id = $request_data['@attributes']['id'];
						if ( 'NT01-00110-311' == $attributes_id ) {
							$status = 'increase';
						} elseif ( 'NT01-00112-311' == $attributes_id ) {
							$status = 'expired';
							$request_data['amount'] = $latest_log['amount'];
						} else {
							$status = 'error';
						}
						$this->save_acting_log( $request_data, 'sbps_paypay', $status, 'OK', $latest_log['order_id'], $request_data['tracking_id'] );
					}
					die( 'OK,' );
				}
			}
		}
	}

	/**
	 * 管理画面決済処理
	 * usces_action_admin_ajax
	 */
	public function admin_ajax() {
		global $usces;

		$mode = sanitize_title( $_POST['mode'] );
		$data = array();

		switch ( $mode ) {
			/* クレジットカード参照 */
			case 'get_sbps_card':
				check_admin_referer( 'order_edit', 'wc_nonce' );
				$order_id = ( isset( $_POST['order_id'] ) ) ? $_POST['order_id'] : '';
				$tracking_id = ( isset( $_POST['tracking_id'] ) ) ? $_POST['tracking_id'] : '';
				if ( empty( $order_id ) || empty( $tracking_id ) ) {
					$data['status'] = 'NG';
					wp_send_json( $data );
					break;
				}

				$res = '';
				$status = '';
				$reference_settlement = $this->get_settlement_status( 'sbps_card', $order_id, $tracking_id );
				// usces_log( print_r( $reference_settlement, true ), "test.log" );
				if ( ( isset( $reference_settlement['res_result'] ) && 'OK' == $reference_settlement['res_result'] ) && ( isset( $reference_settlement['res_status'] ) && 0 == $reference_settlement['res_status'] ) ) {
					$result = 'OK';
					$payment_status = $reference_settlement['res_pay_method_info']['payment_status'];
					switch ( $payment_status ) {
						case 1:/* 与信済 */
							$status = 'manual';
							break;
						case 2:/* 売上済 */
							$status = 'sales';
							break;
						case 3:/* 与信取消済 */
							$status = 'cancel';
							break;
						case 4:/* 返金済 */
							$status = 'cancel';
							break;
						default:
							$status = 'error';
					}

					$latest_log = $this->get_acting_latest_log( $order_id, $tracking_id );
					if ( $status != $latest_log['status'] ) {
						if ( 'cancel' == $status && 'refund' == $latest_log['status'] ) {
							$status = 'refund';
						}
					}

					$class = ' card-' . $status;
					$status_name = $this->get_status_name( $status );
					$res .= '<div class="sbps-settlement-admin' . $class . '">' . $status_name . '</div>';
					if ( 'error' != $status ) {
						$amount = ( 'cancel' == $status ) ? 0 : $this->get_sales_amount( $order_id, $tracking_id );
						if ( 'cancel' == $status || 'refund' == $status ) {
							$res .= '<table class="sbps-settlement-admin-table">
								<tr><th>' . __( 'Settlement amount', 'usces' ) . '</th>
									<td><input type="tel" class="settlement-amount" value="' . intval( $amount ) . '" disabled="disabled" />' . __( usces_crcode( 'return' ), 'usces' ) . '</td>
								</tr></table>';
						} else {
							$res .= '<table class="sbps-settlement-admin-table">
								<tr><th>' . __( 'Settlement amount', 'usces' ) . '</th>
									<td><input type="tel" class="settlement-amount" id="amount_change" value="' . intval( $amount ) . '" />' . __( usces_crcode( 'return' ), 'usces' ) . '<input type="hidden" id="amount_original" value="' . intval( $amount ) . '" /></td>
								</tr></table>';
							$res .= '<div class="sbps-settlement-admin-button">';
							if ( 'manual' == $status ) {
								$res .= '<input id="sales-settlement" type="button" class="button" value="' . __( '売上確定', 'usces' ) . '" />';
							}
							$res .= '<input id="cancel-settlement" type="button" class="button" value="' . __( '取消', 'usces' ) . '" />';
							if ( 'manual' != $status ) {
								$res .= '<input id="refund-settlement" type="button" class="button" value="' . __( '部分返金', 'usces' ) . '" />';
							}
							$res .= '</div>';
						}
					} else {
						$status = 'error';
						$status_name = $this->get_status_name( $status );
						$res .= '<div class="sbps-settlement-admin card-error">' . $status_name . '</div>';
					}
				} else {
					$result = 'NG';
					$status = 'error';
					$status_name = $this->get_status_name( $status );
					$res .= '<div class="sbps-settlement-admin card-error">' . $status_name . '</div>';
				}
				$res .= $this->settlement_history( $order_id, $tracking_id );
				$data['status'] = $result;
				$data['result'] = $res;
				wp_send_json( $data );
				break;

			/* クレジットカード売上確定 */
			case 'sales_sbps_card':
				check_admin_referer( 'order_edit', 'wc_nonce' );
				$order_id = ( isset( $_POST['order_id'] ) ) ? $_POST['order_id'] : '';
				$tracking_id = ( isset( $_POST['tracking_id'] ) ) ? $_POST['tracking_id'] : '';
				$amount = ( isset( $_POST['amount'] ) ) ? $_POST['amount'] : '';
				if ( empty( $order_id ) || empty( $tracking_id ) || empty( $amount ) ) {
					$data['status'] = 'NG';
					wp_send_json( $data );
					break;
				}

				$res = '';
				$acting_status = '';
				$acting_opts = $this->get_acting_settings();
				$connection = $this->get_connection();
				$request_date = date( 'YmdHis', current_time( 'timestamp' ) );
				$sps_hashcode = $acting_opts['merchant_id'] . $acting_opts['service_id'] . $tracking_id . $request_date . $amount . $request_date . $acting_opts['hash_key'];
				$sps_hashcode = sha1( $sps_hashcode );

				/* 売上要求 */
				$request_settlement = '<?xml version="1.0" encoding="Shift_JIS"?>
<sps-api-request id="ST02-00201-101">
	<merchant_id>' . $acting_opts['merchant_id'] . '</merchant_id>
	<service_id>' . $acting_opts['service_id'] . '</service_id>
	<tracking_id>' . $tracking_id . '</tracking_id>
	<processing_datetime>' . $request_date . '</processing_datetime>
	<pay_option_manage>
		<amount>' . $amount . '</amount>
	</pay_option_manage>
	<request_date>' . $request_date . '</request_date>
	<sps_hashcode>' . $sps_hashcode . '</sps_hashcode>
</sps-api-request>';
				$xml_settlement = $this->get_xml_response( $connection['api_url'], $request_settlement );
				if ( $xml_settlement ) {
					$response_settlement = $this->xml2assoc( $xml_settlement );
					$status = 'sales';
					$result = ( isset( $response_settlement['res_result'] ) ) ? $response_settlement['res_result'] : '';
					if ( 'OK' == $result ) {
						if ( ! isset( $response_settlement['amount'] ) ) {
							$response_settlement['amount'] = $amount;
						}
						$this->save_acting_log( $response_settlement, 'sbps_card', $status, $result, $order_id, $tracking_id );
						$class = ' card-' . $status;
						$status_name = $this->get_status_name( $status );
						$res .= '<div class="sbps-settlement-admin' . $class . '">' . $status_name . '</div>';
						$acting_status = '<span class="acting-status' . $class . '">' . $status_name . '</span>';
					} else {
						$latest_log = $this->get_acting_latest_log( $order_id, $tracking_id );
						if ( ! isset( $response_settlement['amount'] ) ) {
							if ( isset( $latest_log['amount'] ) ) {
								$response_settlement['amount'] = $latest_log['amount'];
							}
						}
						$this->save_acting_log( $response_settlement, 'sbps_card', $status, $result, $order_id, $tracking_id );
						$status = $latest_log['status'];
						$class = ' card-' . $status;
						$status_name = $this->get_status_name( $status );
						$res .= '<div class="sbps-settlement-admin' . $class . '">' . $status_name . '</div>';
						$amount = $latest_log['amount'];
					}
					$res .= '<table class="sbps-settlement-admin-table">
						<tr><th>' . __( 'Settlement amount', 'usces' ) . '</th>
							<td><input type="tel" class="settlement-amount" id="amount_change" value="' . intval( $amount ) . '" />' . __( usces_crcode( 'return' ), 'usces' ) . '<input type="hidden" id="amount_original" value="' . intval( $amount ) . '" /></td>
						</tr></table>';
					$res .= '<div class="sbps-settlement-admin-button">';
					if ( 'manual' == $status ) {
						$res .= '<input id="sales-settlement" type="button" class="button" value="' . __( '売上確定', 'usces' ) . '" />';
					}
					if ( ! $this->is_status( array( 'cancel', 'refund' ), $order_id, $tracking_id ) ) {
						$res .= '<input id="cancel-settlement" type="button" class="button" value="' . __( '取消', 'usces' ) . '" />';
						if ( 'manual' != $status ) {
							$res .= '<input id="refund-settlement" type="button" class="button" value="' . __( '部分返金', 'usces' ) . '" />';
						}
					}
					$res .= '</div>';
				} else {
					$result = 'NG';
					$status = 'error';
					$status_name = $this->get_status_name( $status );
					$res .= '<div class="sbps-settlement-admin card-error">' . $status_name . '</div>';
				}
				$res .= $this->settlement_history( $order_id, $tracking_id );
				$data['status'] = $result;
				$data['result'] = $res;
				$data['acting_status'] = $acting_status;
				wp_send_json( $data );
				break;

			/* クレジットカード取消 */
			case 'cancel_sbps_card':
				check_admin_referer( 'order_edit', 'wc_nonce' );
				$order_id = ( isset( $_POST['order_id'] ) ) ? $_POST['order_id'] : '';
				$tracking_id = ( isset( $_POST['tracking_id'] ) ) ? $_POST['tracking_id'] : '';
				if ( empty( $order_id ) || empty( $tracking_id ) ) {
					$data['status'] = 'NG';
					wp_send_json( $data );
					break;
				}

				$res = '';
				$acting_status = '';
				$acting_opts = $this->get_acting_settings();
				$connection = $this->get_connection();
				$request_date = date( 'YmdHis', current_time( 'timestamp' ) );
				$sps_hashcode = $acting_opts['merchant_id'] . $acting_opts['service_id'] . $tracking_id . $request_date . $request_date . $acting_opts['hash_key'];
				$sps_hashcode = sha1( $sps_hashcode );

				/* 取消返金要求 */
				$request_settlement = '<?xml version="1.0" encoding="Shift_JIS"?>
<sps-api-request id="ST02-00303-101">
	<merchant_id>' . $acting_opts['merchant_id'] . '</merchant_id>
	<service_id>' . $acting_opts['service_id'] . '</service_id>
	<tracking_id>' . $tracking_id . '</tracking_id>
	<processing_datetime>' . $request_date . '</processing_datetime>
	<request_date>' . $request_date . '</request_date>
	<sps_hashcode>' . $sps_hashcode . '</sps_hashcode>
</sps-api-request>';
				$xml_settlement = $this->get_xml_response( $connection['api_url'], $request_settlement );
				if ( $xml_settlement ) {
					$response_settlement = $this->xml2assoc( $xml_settlement );
					$status = 'cancel';
					$result = ( isset( $response_settlement['res_result'] ) ) ? $response_settlement['res_result'] : '';
					if ( 'OK' == $result ) {
						$response_settlement['amount'] = 0;
						$this->save_acting_log( $response_settlement, 'sbps_card', $status, $result, $order_id, $tracking_id );
						$class = ' card-' . $status;
						$status_name = $this->get_status_name( $status );
						$res .= '<div class="sbps-settlement-admin' . $class . '">' . $status_name . '</div>';
						$res .= '<table class="sbps-settlement-admin-table">
							<tr><th>' . __( 'Settlement amount', 'usces' ) . '</th>
								<td><input type="tel" class="settlement-amount" value="0" disabled="disabled" />' . __( usces_crcode( 'return' ), 'usces' ) . '</td>
							</tr></table>';
						$acting_status = '<span class="acting-status' . $class . '">' . $status_name . '</span>';
					} else {
						$latest_log = $this->get_acting_latest_log( $order_id, $tracking_id );
						if ( ! isset( $response_settlement['amount'] ) ) {
							if ( isset( $latest_log['amount'] ) ) {
								$response_settlement['amount'] = $latest_log['amount'];
							}
						}
						$this->save_acting_log( $response_settlement, 'sbps_card', $status, $result, $order_id, $tracking_id );
						$status = $latest_log['status'];
						$class = ' card-'.$status;
						$status_name = $this->get_status_name( $status );
						$res .= '<div class="sbps-settlement-admin' . $class . '">' . $status_name . '</div>';
						$res .= '<table class="sbps-settlement-admin-table">
							<tr><th>' . __( 'Settlement amount', 'usces' ) . '</th>
								<td><input type="tel" class="settlement-amount" id="amount_change" value="' . intval( $latest_log['amount'] ) . '" />' . __( usces_crcode( 'return' ), 'usces' ) . '<input type="hidden" id="amount_original" value="' . intval( $latest_log['amount'] ) . '" /></td>
							</tr></table>';
						$res .= '<div class="sbps-settlement-admin-button">';
						if ( 'manual' == $status ) {
							$res .= '<input id="sales-settlement" type="button" class="button" value="' . __( '売上確定', 'usces' ) . '" />';
						}
						if ( ! $this->is_status( array( 'cancel', 'refund' ), $order_id, $tracking_id ) ) {
							$res .= '<input id="cancel-settlement" type="button" class="button" value="' . __( '取消', 'usces' ) . '" />';
							if ( 'manual' != $status ) {
								$res .= '<input id="refund-settlement" type="button" class="button" value="' . __( '部分返金', 'usces' ) . '" />';
							}
						}
						$res .= '</div>';
					}
				} else {
					$result = 'NG';
				}
				$res .= $this->settlement_history( $order_id, $tracking_id );
				$data['status'] = $result;
				$data['result'] = $res;
				$data['acting_status'] = $acting_status;
				wp_send_json( $data );
				break;

			/* クレジットカード部分返金 */
			case 'refund_sbps_card':
				check_admin_referer( 'order_edit', 'wc_nonce' );
				$order_id = ( isset( $_POST['order_id'] ) ) ? $_POST['order_id'] : '';
				$tracking_id = ( isset( $_POST['tracking_id'] ) ) ? $_POST['tracking_id'] : '';
				$amount = ( isset( $_POST['amount'] ) ) ? $_POST['amount'] : '';
				if ( empty( $order_id ) || empty( $tracking_id ) || empty( $amount ) ) {
					$data['status'] = 'NG';
					wp_send_json( $data );
					break;
				}

				$res = '';
				$acting_status = '';
				$acting_opts = $this->get_acting_settings();
				$connection = $this->get_connection();
				$request_date = date( 'YmdHis', current_time( 'timestamp' ) );
				$sps_hashcode = $acting_opts['merchant_id'] . $acting_opts['service_id'] . $tracking_id . $request_date . $amount . $request_date . $acting_opts['hash_key'];
				$sps_hashcode = sha1( $sps_hashcode );

				/* 部分返金要求 */
				$request_settlement = '<?xml version="1.0" encoding="Shift_JIS"?>
<sps-api-request id="ST02-00307-101">
	<merchant_id>' . $acting_opts['merchant_id'] . '</merchant_id>
	<service_id>' . $acting_opts['service_id'] . '</service_id>
	<tracking_id>' . $tracking_id . '</tracking_id>
	<processing_datetime>' . $request_date . '</processing_datetime>
	<pay_option_manage>
		<amount>' . $amount . '</amount>
	</pay_option_manage>
	<request_date>' . $request_date . '</request_date>
	<sps_hashcode>' . $sps_hashcode . '</sps_hashcode>
</sps-api-request>';
				$xml_settlement = $this->get_xml_response( $connection['api_url'], $request_settlement );
				if ( $xml_settlement ) {
					$response_settlement = $this->xml2assoc( $xml_settlement );
					$status = 'refund';
					$result = ( isset( $response_settlement['res_result'] ) ) ? $response_settlement['res_result'] : '';
					if ( 'OK' == $result ) {
						$response_settlement['amount'] = $amount * -1;
						$this->save_acting_log( $response_settlement, 'sbps_card', $status, $result, $order_id, $tracking_id );
						$class = ' card-' . $status;
						$status_name = $this->get_status_name( $status );
						$sales_amount = $this->get_sales_amount( $order_id, $tracking_id );
						$res .= '<div class="sbps-settlement-admin' . $class . '">' . $status_name . '</div>';
						$res .= '<table class="sbps-settlement-admin-table">
							<tr><th>' . __( 'Settlement amount', 'usces' ) . '</th>
								<td><input type="tel" class="settlement-amount" value="' . intval( $sales_amount ) . '" disabled="disabled" />' . __( usces_crcode( 'return' ), 'usces' ) . '</td>
							</tr></table>';
						$acting_status = '<span class="acting-status' . $class . '">' . $status_name . '</span>';
					} else {
						$latest_log = $this->get_acting_latest_log( $order_id, $tracking_id );
						if ( ! isset( $response_settlement['amount'] ) ) {
							if ( isset( $latest_log['amount'] ) ) {
								$response_settlement['amount'] = $latest_log['amount'];
							}
						}
						$this->save_acting_log( $response_settlement, 'sbps_card', $status, $result, $order_id, $tracking_id );
						$status = $latest_log['status'];
						$class = ' card-' . $status;
						$status_name = $this->get_status_name( $status );
						$res .= '<div class="sbps-settlement-admin' . $class . '">' . $status_name . '</div>';
						$res .= '<table class="sbps-settlement-admin-table">
							<tr><th>' . __( 'Settlement amount', 'usces' ) . '</th>
								<td><input type="tel" value="' . $latest_log['amount'] . '" />' . __( usces_crcode( 'return' ), 'usces' ) . '</td>
							</tr></table>';
						$res .= '<div class="sbps-settlement-admin-button">';
						if ( ! $this->is_status( array( 'cancel', 'refund' ), $order_id, $tracking_id ) ) {
							$res .= '<input id="cancel-settlement" type="button" class="button" value="' . __( '取消', 'usces' ) . '" />';
							$res .= '<input id="refund-settlement" type="button" class="button" value="' . __( '部分返金', 'usces' ) . '" />';
						}
						$res .= '</div>';
					}
				} else {
					$result = 'NG';
				}
				$res .= $this->settlement_history( $order_id, $tracking_id );
				$data['status'] = $result;
				$data['result'] = $res;
				$data['acting_status'] = $acting_status;
				wp_send_json( $data );
				break;

			/* クレジットカード決済エラー */
			case 'error_sbps_card':
				break;

			/* PayPay参照 */
			case 'get_sbps_paypay':
				check_admin_referer( 'order_edit', 'wc_nonce' );
				$order_id = ( isset( $_POST['order_id'] ) ) ? $_POST['order_id'] : '';
				$tracking_id = ( isset( $_POST['tracking_id'] ) ) ? $_POST['tracking_id'] : '';
				if ( empty( $order_id ) || empty( $tracking_id ) ) {
					$data['status'] = 'NG';
					wp_send_json( $data );
					break;
				}

				$res = '';
				$status = '';
				$reference_settlement = $this->get_settlement_status( 'sbps_paypay', $order_id, $tracking_id );
				// usces_log( print_r( $reference_settlement, true), "test.log" );
				if ( ( isset( $reference_settlement['res_result'] ) && 'OK' == $reference_settlement['res_result'] ) && ( isset( $reference_settlement['res_status'] ) && 0 == $reference_settlement['res_status'] ) ) {
					$result = 'OK';
					$payment_status = $reference_settlement['res_pay_method_info']['payment_status'];
					switch ( $payment_status ) {
						case 1:/* 与信済 */
							$status = 'manual';
							break;
						case 0:/* 処理中 */
						case 2:/* 売上処理中 */
							$status = 'pending';
							break;
						case 3:/* 入金済（売上済） */
							$status = 'sales';
							break;
						case 4:/* 与信取消済 */
						case 5:/* 返金済 */
							$status = 'cancel';
							break;
						case 9:/* 処理エラー */
							$status = 'error';
							break;
					}

					$latest_log = $this->get_acting_latest_log( $order_id, $tracking_id );
					if ( $status != $latest_log['status'] ) {
						if ( 'cancel' == $status && 'refund' == $latest_log['status'] ) {
							$status = 'refund';
						}
					}

					$class = ' paypay-' . $status;
					$status_name = $this->get_status_name( $status );
					$res .= '<div class="sbps-settlement-admin' . $class . '">' . $status_name . '</div>';
					if ( 'pending' != $status && 'error' != $status ) {
						$amount = ( 'cancel' == $status ) ? 0 : $this->get_sales_amount( $order_id, $tracking_id );
						if ( 'cancel' == $status || 'refund' == $status ) {
							$res .= '<table class="sbps-settlement-admin-table">
								<tr><th>' . __( 'Settlement amount', 'usces' ) . '</th>
									<td><input type="tel" class="settlement-amount" value="' . intval( $amount ) . '" disabled="disabled" />' . __( usces_crcode( 'return' ), 'usces' ) . '</td>
								</tr></table>';
						} else {
							$res .= '<table class="sbps-settlement-admin-table">
								<tr><th>' . __( 'Settlement amount', 'usces' ) . '</th>
									<td><input type="tel" class="settlement-amount" id="amount_change" value="' . intval( $amount ) . '" />' . __( usces_crcode( 'return' ), 'usces' ) . '<input type="hidden" id="amount_original" value="' . intval( $amount ) . '" /></td>
								</tr></table>';
							$res .= '<div class="sbps-settlement-admin-button">';
							if ( 'manual' == $status ) {
								$res .= '<input id="sales-settlement" type="button" class="button" value="' . __( '売上確定', 'usces' ) . '" />';
							}
							$res .= '<input id="cancel-settlement" type="button" class="button" value="' . __( '取消', 'usces' ) . '" />';
							if ( 'manual' != $status ) {
								$res .= '<input id="refund-settlement" type="button" class="button" value="' . __( '部分返金', 'usces' ) . '" />';
							}
							if ( ! $this->is_status( array( 'auto', 'sales', 'increase' ), $order_id, $tracking_id ) ) {
								$res .= '<input id="increase-settlement" type="button" class="button" value="' . __( '増額売上', 'usces' ) . '" />';
							}
							$res .= '</div>';
						}
					} else {
						if ( 'pending' != $status ) {
							$status = 'error';
							$status_name = $this->get_status_name( $status );
							$res .= '<div class="sbps-settlement-admin paypay-error">' . $status_name . '</div>';
						}
					}
				} else {
					$result = 'NG';
					$status = 'error';
					$status_name = $this->get_status_name( $status );
					$res .= '<div class="sbps-settlement-admin paypay-error">' . $status_name . '</div>';
				}
				$res .= $this->settlement_history( $order_id, $tracking_id );
				$data['status'] = $result;
				$data['result'] = $res;
				wp_send_json( $data );
				break;

			/* PayPay売上確定 */
			case 'sales_sbps_paypay':
				check_admin_referer( 'order_edit', 'wc_nonce' );
				$order_id = ( isset( $_POST['order_id'] ) ) ? $_POST['order_id'] : '';
				$tracking_id = ( isset( $_POST['tracking_id'] ) ) ? $_POST['tracking_id'] : '';
				$amount = ( isset( $_POST['amount'] ) ) ? $_POST['amount'] : '';
				if ( empty( $order_id ) || empty( $tracking_id ) || empty( $amount ) ) {
					$data['status'] = 'NG';
					wp_send_json( $data );
					break;
				}

				$res = '';
				$acting_status = '';
				$acting_opts = $this->get_acting_settings();
				$connection = $this->get_connection();
				$request_date = date( 'YmdHis', current_time( 'timestamp' ) );
				$sps_hashcode = $acting_opts['merchant_id'] . $acting_opts['service_id'] . $tracking_id . $amount . $request_date . $acting_opts['hash_key'];
				$sps_hashcode = sha1( $sps_hashcode );

				/* 売上要求 */
				$request_settlement = '<?xml version="1.0" encoding="Shift_JIS"?>
<sps-api-request id="ST02-00201-311">
	<merchant_id>' . $acting_opts['merchant_id'] . '</merchant_id>
	<service_id>' . $acting_opts['service_id'] . '</service_id>
	<tracking_id>' . $tracking_id . '</tracking_id>
	<pay_option_manage>
		<amount>' . $amount . '</amount>
	</pay_option_manage>
	<request_date>' . $request_date . '</request_date>
	<sps_hashcode>' . $sps_hashcode . '</sps_hashcode>
</sps-api-request>';
				$xml_settlement = $this->get_xml_response( $connection['api_url'], $request_settlement );
				if ( $xml_settlement ) {
					$response_settlement = $this->xml2assoc( $xml_settlement );
					$status = 'sales';
					$result = ( isset( $response_settlement['res_result'] ) ) ? $response_settlement['res_result'] : '';
					if ( 'OK' == $result ) {
						if ( ! isset( $response_settlement['amount'] ) ) {
							$response_settlement['amount'] = $amount;
						}
						$this->save_acting_log( $response_settlement, 'sbps_paypay', $status, $result, $order_id, $tracking_id );
						$class = ' paypay-' . $status;
						$status_name = $this->get_status_name( $status );
						$res .= '<div class="sbps-settlement-admin' . $class . '">' . $status_name . '</div>';
						$acting_status = '<span class="acting-status' . $class . '">' . $status_name . '</span>';
					} else {
						$latest_log = $this->get_acting_latest_log( $order_id, $tracking_id );
						if ( ! isset( $response_settlement['amount'] ) ) {
							if ( isset( $latest_log['amount'] ) ) {
								$response_settlement['amount'] = $latest_log['amount'];
							}
						}
						$this->save_acting_log( $response_settlement, 'sbps_paypay', $status, $result, $order_id, $tracking_id );
						$status = $latest_log['status'];
						$class = ' paypay-' . $status;
						$status_name = $this->get_status_name( $status );
						$res .= '<div class="sbps-settlement-admin' . $class . '">' . $status_name . '</div>';
						$amount = $latest_log['amount'];
					}
					$res .= '<table class="sbps-settlement-admin-table">
						<tr><th>' . __( 'Settlement amount', 'usces' ) . '</th>
							<td><input type="tel" class="settlement-amount" id="amount_change" value="' . intval( $amount ) . '" />' . __( usces_crcode( 'return' ), 'usces' ) . '<input type="hidden" id="amount_original" value="' . intval( $amount ) . '" /></td>
						</tr></table>';
					$res .= '<div class="sbps-settlement-admin-button">';
					if ( 'manual' == $status ) {
						$res .= '<input id="sales-settlement" type="button" class="button" value="' . __( '売上確定', 'usces' ) . '" />';
					}
					if ( ! $this->is_status( array( 'cancel', 'refund' ), $order_id, $tracking_id ) ) {
						$res .= '<input id="cancel-settlement" type="button" class="button" value="' . __( '取消', 'usces' ) . '" />';
						if ( 'manual' != $status ) {
							$res .= '<input id="refund-settlement" type="button" class="button" value="' . __( '部分返金', 'usces' ) . '" />';
						}
						if ( ! $this->is_status( array( 'auto', 'sales', 'increase' ), $order_id, $tracking_id ) ) {
							$res .= '<input id="increase-settlement" type="button" class="button" value="' . __( '増額売上', 'usces' ) . '" />';
						}
					}
					$res .= '</div>';
				} else {
					$result = 'NG';
					$status = 'error';
					$status_name = $this->get_status_name( $status );
					$res .= '<div class="sbps-settlement-admin card-error">' . $status_name . '</div>';
				}
				$res .= $this->settlement_history( $order_id, $tracking_id );
				$data['status'] = $result;
				$data['result'] = $res;
				$data['acting_status'] = $acting_status;
				wp_send_json( $data );
				break;

			/* PayPay取消 */
			case 'cancel_sbps_paypay':
				check_admin_referer( 'order_edit', 'wc_nonce' );
				$order_id = ( isset( $_POST['order_id'] ) ) ? $_POST['order_id'] : '';
				$tracking_id = ( isset( $_POST['tracking_id'] ) ) ? $_POST['tracking_id'] : '';
				if ( empty( $order_id ) || empty( $tracking_id ) ) {
					$data['status'] = 'NG';
					wp_send_json( $data );
					break;
				}

				$res = '';
				$acting_status = '';
				$acting_opts = $this->get_acting_settings();
				$connection = $this->get_connection();
				$encrypted_flg = '1';
				$request_date = date( 'YmdHis', current_time( 'timestamp' ) );
				$sps_hashcode = $acting_opts['merchant_id'] . $acting_opts['service_id'] . $tracking_id . $encrypted_flg . $request_date . $acting_opts['hash_key'];
				$sps_hashcode = sha1( $sps_hashcode );

				/* 取消返金要求 */
				$request_settlement = '<?xml version="1.0" encoding="Shift_JIS"?>
<sps-api-request id="ST02-00303-311">
	<merchant_id>' . $acting_opts['merchant_id'] . '</merchant_id>
	<service_id>' . $acting_opts['service_id'] . '</service_id>
	<tracking_id>' . $tracking_id . '</tracking_id>
	<encrypted_flg>' . $encrypted_flg . '</encrypted_flg>
	<request_date>' . $request_date . '</request_date>
	<sps_hashcode>' . $sps_hashcode . '</sps_hashcode>
</sps-api-request>';
				$xml_settlement = $this->get_xml_response( $connection['api_url'], $request_settlement );
				if ( $xml_settlement ) {
					$response_settlement = $this->xml2assoc( $xml_settlement, $encrypted_flg );
					$status = 'cancel';
					$result = ( isset( $response_settlement['res_result'] ) ) ? $response_settlement['res_result'] : '';
					if ( 'OK' == $result ) {
						$response_settlement['amount'] = 0;
						$this->save_acting_log( $response_settlement, 'sbps_paypay', $status, $result, $order_id, $tracking_id );
						$class = ' paypay-' . $status;
						$status_name = $this->get_status_name( $status );
						$res .= '<div class="sbps-settlement-admin' . $class . '">' . $status_name . '</div>';
						$acting_status = '<span class="acting-status' . $class . '">' . $status_name . '</span>';
						$res .= '<table class="sbps-settlement-admin-table">
							<tr><th>' . __( 'Settlement amount', 'usces' ) . '</th>
								<td><input type="tel" class="settlement-amount" value="0" disabled="disabled" />' . __( usces_crcode( 'return' ), 'usces' ) . '</td>
							</tr></table>';
						$acting_status = '<span class="acting-status' . $class . '">' . $status_name . '</span>';
					} else {
						$latest_log = $this->get_acting_latest_log( $order_id, $tracking_id );
						if ( ! isset( $response_settlement['amount'] ) ) {
							if ( isset( $latest_log['amount'] ) ) {
								$response_settlement['amount'] = $latest_log['amount'];
							}
						}
						$this->save_acting_log( $response_settlement, 'sbps_paypay', $status, $result, $order_id, $tracking_id );
						$status = $latest_log['status'];
						$class = ' paypay-' . $status;
						$status_name = $this->get_status_name( $status );
						$res .= '<div class="sbps-settlement-admin' . $class . '">' . $status_name . '</div>';
						$res .= '<table class="sbps-settlement-admin-table">
							<tr><th>' . __( 'Settlement amount', 'usces' ) . '</th>
								<td><input type="tel" class="settlement-amount" id="amount_change" value="' . intval( $latest_log['amount'] ) . '" />' . __( usces_crcode( 'return' ), 'usces' ) . '<input type="hidden" id="amount_original" value="' . intval( $latest_log['amount'] ) . '" /></td>
							</tr></table>';
						$res .= '<div class="sbps-settlement-admin-button">';
						if ( 'manual' == $status ) {
							$res .= '<input id="sales-settlement" type="button" class="button" value="' . __( '売上確定', 'usces' ) . '" />';
						}
						if ( ! $this->is_status( array( 'cancel', 'refund' ), $order_id, $tracking_id ) ) {
							$res .= '<input id="cancel-settlement" type="button" class="button" value="' . __( '取消', 'usces' ) . '" />';
							if ( 'manual' != $status ) {
								$res .= '<input id="refund-settlement" type="button" class="button" value="' . __( '部分返金', 'usces' ) . '" />';
							}
							if ( ! $this->is_status( array( 'auto', 'sales', 'increase' ), $order_id, $tracking_id ) ) {
								$res .= '<input id="increase-settlement" type="button" class="button" value="' . __( '増額売上', 'usces' ) . '" />';
							}
						}
						$res .= '</div>';
					}
				} else {
					$result = 'NG';
				}
				$res .= $this->settlement_history( $order_id, $tracking_id );
				$data['status'] = $result;
				$data['result'] = $res;
				$data['acting_status'] = $acting_status;
				wp_send_json( $data );
				break;

			/* PayPay返金 */
			case 'refund_sbps_paypay':
				check_admin_referer( 'order_edit', 'wc_nonce' );
				$order_id = ( isset( $_POST['order_id'] ) ) ? $_POST['order_id'] : '';
				$tracking_id = ( isset( $_POST['tracking_id'] ) ) ? $_POST['tracking_id'] : '';
				$amount = ( isset( $_POST['amount'] ) ) ? $_POST['amount'] : '';
				if ( empty( $order_id ) || empty( $tracking_id ) || empty( $amount ) ) {
					$data['status'] = 'NG';
					wp_send_json( $data );
					break;
				}

				$res = '';
				$acting_status = '';
				$acting_opts = $this->get_acting_settings();
				$connection = $this->get_connection();
				$request_date = date( 'YmdHis', current_time( 'timestamp' ) );
				$sps_hashcode = $acting_opts['merchant_id'] . $acting_opts['service_id'] . $tracking_id . $amount . $request_date . $acting_opts['hash_key'];
				$sps_hashcode = sha1( $sps_hashcode );

				/* 返金要求 */
				$request_settlement = '<?xml version="1.0" encoding="Shift_JIS"?>
<sps-api-request id="ST02-00306-311">
	<merchant_id>' . $acting_opts['merchant_id'] . '</merchant_id>
	<service_id>' . $acting_opts['service_id'] . '</service_id>
	<tracking_id>' . $tracking_id . '</tracking_id>
	<pay_option_manage>
		<amount>' . $amount . '</amount>
	</pay_option_manage>
	<request_date>' . $request_date . '</request_date>
	<sps_hashcode>' . $sps_hashcode . '</sps_hashcode>
</sps-api-request>';
				$xml_settlement = $this->get_xml_response( $connection['api_url'], $request_settlement );
				if ( $xml_settlement ) {
					$response_settlement = $this->xml2assoc( $xml_settlement );
					$status = 'refund';
					$result = ( isset( $response_settlement['res_result'] ) ) ? $response_settlement['res_result'] : '';
					if ( 'OK' == $result ) {
						$response_settlement['amount'] = $amount * -1;
						$this->save_acting_log( $response_settlement, 'sbps_paypay', $status, $result, $order_id, $tracking_id );
						$class = ' paypay-' . $status;
						$status_name = $this->get_status_name( $status );
						$sales_amount = $this->get_sales_amount( $order_id, $tracking_id );
						$res .= '<div class="sbps-settlement-admin' . $class . '">' . $status_name . '</div>';
						$res .= '<table class="sbps-settlement-admin-table">
							<tr><th>' . __( 'Settlement amount', 'usces' ) . '</th>
								<td><input type="tel" class="settlement-amount" value="' . intval( $sales_amount ) . '" disabled="disabled" />' . __( usces_crcode( 'return' ), 'usces' ) . '</td>
							</tr></table>';
						$acting_status = '<span class="acting-status' . $class . '">' . $status_name . '</span>';
					} else {
						$latest_log = $this->get_acting_latest_log( $order_id, $tracking_id );
						if ( ! isset( $response_settlement['amount'] ) ) {
							if ( isset( $latest_log['amount'] ) ) {
								$response_settlement['amount'] = $latest_log['amount'];
							}
						}
						$this->save_acting_log( $response_settlement, 'sbps_paypay', $status, $result, $order_id, $tracking_id );
						$status = $latest_log['status'];
						$class = ' paypay-' . $status;
						$status_name = $this->get_status_name( $status );
						$res .= '<div class="sbps-settlement-admin' . $class . '">' . $status_name . '</div>';
						$res .= '<table class="sbps-settlement-admin-table">
							<tr><th>' . __( 'Settlement amount', 'usces' ) . '</th>
								<td><input type="tel" class="settlement-amount" id="amount_change" value="' . intval( $latest_log['amount'] ) . '" />' . __( usces_crcode( 'return' ), 'usces' ) . '<input type="hidden" id="amount_original" value="' . intval( $latest_log['amount'] ) . '" /></td>
							</tr></table>';
						$res .= '<div class="sbps-settlement-admin-button">';
						if ( ! $this->is_status( array( 'cancel', 'refund' ), $order_id, $tracking_id ) ) {
							$res .= '<input id="cancel-settlement" type="button" class="button" value="' . __( '取消', 'usces' ) . '" />';
							if ( 'manual' != $status ) {
								$res .= '<input id="refund-settlement" type="button" class="button" value="' . __( '部分返金', 'usces' ) . '" />';
							}
							if ( ! $this->is_status( array( 'auto', 'sales', 'increase' ), $order_id, $tracking_id ) ) {
								$res .= '<input id="increase-settlement" type="button" class="button" value="' . __( '増額売上', 'usces' ) . '" />';
							}
						}
						$res .= '</div>';
					}
				} else {
					$result = 'NG';
				}
				$res .= $this->settlement_history( $order_id, $tracking_id );
				$data['status'] = $result;
				$data['result'] = $res;
				$data['acting_status'] = $acting_status;
				wp_send_json( $data );
				break;

			/* PayPay増額売上 */
			case 'increase_sbps_paypay':
				check_admin_referer( 'order_edit', 'wc_nonce' );
				$order_id = ( isset( $_POST['order_id'] ) ) ? $_POST['order_id'] : '';
				$tracking_id = ( isset( $_POST['tracking_id'] ) ) ? $_POST['tracking_id'] : '';
				$amount = ( isset( $_POST['amount'] ) ) ? $_POST['amount'] : '';
				if ( empty( $order_id ) || empty( $tracking_id ) || empty( $amount ) ) {
					$data['status'] = 'NG';
					wp_send_json( $data );
					break;
				}

				$res = '';
				$acting_status = '';
				$acting_opts = $this->get_acting_settings();
				$connection = $this->get_connection();
				$request_date = date( 'YmdHis', current_time( 'timestamp' ) );
				$sps_hashcode = $acting_opts['merchant_id'] . $acting_opts['service_id'] . $tracking_id . $amount . $request_date . $acting_opts['hash_key'];
				$sps_hashcode = sha1( $sps_hashcode );

				/* 売上要求 */
				$request_settlement = '<?xml version="1.0" encoding="Shift_JIS"?>
<sps-api-request id="ST02-00201-311">
	<merchant_id>' . $acting_opts['merchant_id'] . '</merchant_id>
	<service_id>' . $acting_opts['service_id'] . '</service_id>
	<tracking_id>' . $tracking_id . '</tracking_id>
	<pay_option_manage>
		<amount>' . $amount . '</amount>
	</pay_option_manage>
	<request_date>' . $request_date . '</request_date>
	<sps_hashcode>' . $sps_hashcode . '</sps_hashcode>
</sps-api-request>';
				$xml_settlement = $this->get_xml_response( $connection['api_url'], $request_settlement );
				if ( $xml_settlement ) {
					$response_settlement = $this->xml2assoc( $xml_settlement );
					// usces_log( "response_settlement=" . print_r( $response_settlement, true ), "test.log" );
					$result = ( isset( $response_settlement['res_result'] ) ) ? $response_settlement['res_result'] : '';
					if ( 'OK' == $result ) {
						if ( ! isset( $response_settlement['amount'] ) ) {
							$response_settlement['amount'] = $amount;
						}
						$status = 'increase';
						$this->save_acting_log( $response_settlement, 'sbps_paypay', $status, $result, $order_id, $tracking_id );
						$class = ' paypay-' . $status;
						$status_name = $this->get_status_name( $status );
						$res .= '<div class="sbps-settlement-admin' . $class . '">' . $status_name . '</div>';
						$res .= '<table class="sbps-settlement-admin-table">
							<tr><th>' . __( 'Settlement amount', 'usces' ) . '</th>
								<td><input type="tel" class="settlement-amount" id="amount_change" value="' . intval( $amount ) . '" />' . __( usces_crcode( 'return' ), 'usces' ) . '<input type="hidden" id="amount_original" value="' . intval( $amount ) . '" /></td>
							</tr></table>';
						$res .= '<div class="sbps-settlement-admin-button">';
						$res .= '<input id="sales-settlement" type="button" class="button" value="' . __( '売上確定', 'usces' ) . '" />';
						$res .= '<input id="cancel-settlement" type="button" class="button" value="' . __( '取消', 'usces' ) . '" />';
						$res .= '</div>';
						$acting_status = '<span class="acting-status' . $class . '">' . $status_name . '</span>';
					} elseif ( 'AC' == $result ) {
						$status = 'pending';
						if ( ! isset( $response_settlement['amount'] ) ) {
							$response_settlement['amount'] = $amount;
						}
						$this->save_acting_log( $response_settlement, 'sbps_paypay', $status, $result, $order_id, $tracking_id );
						$class = ' paypay-' . $status;
						$status_name = $this->get_status_name( $status );
						$res .= '<div class="sbps-settlement-admin' . $class . '">' . $status_name . '</div>';
						$acting_status = '<span class="acting-status' . $class . '">' . $status_name . '</span>';
					} else {
						if ( ! isset( $response_settlement['amount'] ) ) {
							$response_settlement['amount'] = $amount;
						}
						$this->save_acting_log( $response_settlement, 'sbps_paypay', 'pending', $result, $order_id, $tracking_id );
						$latest_log = $this->get_acting_latest_log( $order_id, $tracking_id );
						$status = $latest_log['status'];
						$class = ' paypay-' . $status;
						$status_name = $this->get_status_name( $status );
						$res .= '<div class="sbps-settlement-admin' . $class . '">' . $status_name . '</div>';
						$res .= '<table class="sbps-settlement-admin-table">
							<tr><th>' . __( 'Settlement amount', 'usces' ) . '</th>
								<td><input type="tel" class="settlement-amount" id="amount_change" value="' . intval( $latest_log['amount'] ) . '" />' . __( usces_crcode( 'return' ), 'usces' ) . '<input type="hidden" id="amount_original" value="' . intval( $latest_log['amount'] ) . '" /></td>
							</tr></table>';
						$res .= '<div class="sbps-settlement-admin-button">';
						if ( 'manual' == $status ) {
							$res .= '<input id="sales-settlement" type="button" class="button" value="' . __( '売上確定', 'usces' ) . '" />';
						}
						if ( ! $this->is_status( array( 'cancel', 'refund' ), $order_id, $tracking_id ) ) {
							$res .= '<input id="cancel-settlement" type="button" class="button" value="' . __( '取消', 'usces' ) . '" />';
							if ( 'manual' != $status ) {
								$res .= '<input id="refund-settlement" type="button" class="button" value="' . __( '部分返金', 'usces' ) . '" />';
							}
							if ( ! $this->is_status( array( 'auto', 'sales', 'increase' ), $order_id, $tracking_id ) ) {
								$res .= '<input id="increase-settlement" type="button" class="button" value="' . __( '増額売上', 'usces' ) . '" />';
							}
						}
						$res .= '</div>';
					}
				} else {
					$result = 'NG';
				}
				$res .= $this->settlement_history( $order_id, $tracking_id );
				$data['status'] = $result;
				$data['result'] = $res;
				$data['acting_status'] = $acting_status;
				wp_send_json( $data );
				break;
		}
	}

	/**
	 * 受注編集画面に表示する決済情報の値整形
	 * usces_filter_settle_info_field_value
	 *
	 * @param  string $value
	 * @param  string $key
	 * @param  string $acting Acting type.
	 * @return string
	 */
	public function settlement_info_field_value( $value, $key, $acting ) {
		if ( ! in_array( 'acting_' . $acting, $this->pay_method ) ) {
			return $value;
		}
		$value = parent::settlement_info_field_value( $value, $key, $acting );

		return $value;
	}

	/**
	 * 決済状況
	 * usces_filter_orderlist_detail_value
	 *
	 * @param  string $detail HTML.
	 * @param  string $value
	 * @param  string $key
	 * @param  int    $order_id Order number.
	 * @return array
	 */
	public function orderlist_settlement_status( $detail, $value, $key, $order_id ) {
		global $usces;

		if ( 'wc_trans_id' != $key || empty( $value ) ) {
			return $detail;
		}

		$order_data = $usces->get_order_data( $order_id, 'direct' );
		$payment = usces_get_payments_by_name( $order_data['order_payment_name'] );
		$acting_flg = ( isset( $payment['settlement'] ) ) ? $payment['settlement'] : '';
		if ( 'acting_sbps_card' == $acting_flg || 'acting_sbps_paypay' == $acting_flg ) {
			$tracking_id = $usces->get_order_meta_value( 'res_tracking_id', $order_id );
			$acting_status = $this->get_acting_status( $order_id, $tracking_id );
			if ( ! empty( $acting_status ) ) {
				$status_name = '';
				$class = '';
				switch ( $acting_flg ) {
					case 'acting_sbps_card':
						$class = ' card-' . $acting_status;
						$status_name = $this->get_status_name( $acting_status );
						break;
					case 'acting_sbps_paypay':
						$class = ' paypay-' . $acting_status;
						$status_name = $this->get_status_name( $acting_status );
						break;
				}
				$detail = '<td>' . $value . '<span class="acting-status' . $class . '">' . $status_name . '</span></td>';
			}
		} elseif ( 'acting_sbps_paypay' == $acting_flg ) {
		}
		return $detail;
	}

	/**
	 * 受注編集画面【ステータス】
	 * usces_action_order_edit_form_status_block_middle
	 *
	 * @param  array $data Order data.
	 * @param  array $cscs_meta
	 * @param  array $action_args Compact array( 'order_action', 'order_id', 'cart' ).
	 */
	public function settlement_status( $data, $cscs_meta, $action_args ) {
		global $usces;
		extract( $action_args );

		if ( 'new' != $order_action && ! empty( $order_id ) ) {
			$payment = usces_get_payments_by_name( $data['order_payment_name'] );
			$acting_flg = ( isset( $payment['settlement'] ) ) ? $payment['settlement'] : '';
			if ( 'acting_sbps_card' == $acting_flg || 'acting_sbps_paypay' == $acting_flg ) {
				$tracking_id = $usces->get_order_meta_value( 'res_tracking_id', $order_id );
				$acting_status = $this->get_acting_status( $order_id, $tracking_id );
				if ( ! empty( $acting_status ) ) {
					$status_name = '';
					$class = '';
					switch ( $acting_flg ) {
						case 'acting_sbps_card':
							$class = ' card-' . $acting_status;
							$status_name = $this->get_status_name( $acting_status );
							break;
						case 'acting_sbps_paypay':
							$class = ' paypay-' . $acting_status;
							$status_name = $this->get_status_name( $acting_status );
							break;
					}
					if ( ! empty( $status_name ) ) {
						echo '
						<tr>
							<td class="label status">' . __( 'Settlement status', 'usces' ) . '</td>
							<td class="col1 status"><span id="settlement-status"><span class="acting-status' . $class . '">' . $status_name . '</span></span></td>
						</tr>';
					}
				}
			}
		}
	}

	/**
	 * 受注編集画面【支払情報】
	 * usces_action_order_edit_form_settle_info
	 *
	 * @param  array $data Order data.
	 * @param  array $action_args Compact array( 'order_action', 'order_id', 'cart' ).
	 */
	public function settlement_information( $data, $action_args ) {
		global $usces;
		extract( $action_args );

		if ( 'new' != $order_action && ! empty( $order_id ) ) {
			$payment = usces_get_payments_by_name( $data['order_payment_name'] );
			if ( 'acting_sbps_card' == $payment['settlement'] || 'acting_sbps_paypay' == $payment['settlement'] ) {
				$acting_data = usces_unserialize( $usces->get_order_meta_value( $payment['settlement'], $order_id ) );
				$tracking_id = ( isset( $acting_data['res_tracking_id'] ) ) ? $acting_data['res_tracking_id'] : '';
				echo '<input type="button" class="button settlement-information" id="settlement-information-' . $tracking_id . '" data-tracking_id="' . $tracking_id . '" value="' . __( 'Settlement info', 'usces' ) . '">';
			}
		}
	}

	/**
	 * 決済情報ダイアログ
	 * usces_action_endof_order_edit_form
	 *
	 * @param  array $data Order data.
	 * @param  array $action_args Compact array( 'order_action', 'order_id', 'cart' ).
	 */
	public function settlement_dialog( $data, $action_args ) {
		global $usces;
		extract( $action_args );

		if ( 'new' != $order_action && ! empty( $order_id ) ) :
			$payment = usces_get_payments_by_name( $data['order_payment_name'] );
			if ( in_array( $payment['settlement'], $this->pay_method ) ) :
				?>
<div id="settlement_dialog" title="">
	<div id="settlement-response-loading"></div>
	<fieldset>
	<div id="settlement-response"></div>
	<input type="hidden" id="order_num">
	<input type="hidden" id="tracking_id">
	<input type="hidden" id="acting" value="<?php echo esc_html( $payment['settlement'] ); ?>">
	<input type="hidden" id="error">
	</fieldset>
</div>
				<?php
			endif;
		endif;
	}

	/**
	 * 受注データ登録
	 * Call from usces_reg_orderdata() and usces_new_orderdata().
	 * usces_action_reg_orderdata
	 *
	 * @param  array $args Compact array( 'cart', 'entry', 'order_id', 'member_id', 'payments', 'charging_type', 'results' ).
	 */
	public function register_orderdata( $args ) {
		extract( $args );

		$acting_flg = $payments['settlement'];
		if ( ! in_array( $acting_flg, $this->pay_method ) ) {
			return;
		}
		if ( ! $entry['order']['total_full_price'] ) {
			return;
		}

		parent::register_orderdata( $args );

		if ( 'acting_sbps_card' == $acting_flg || 'acting_sbps_paypay' == $acting_flg ) {
			$acting_opts = $this->get_acting_settings();
			$acting = substr( $acting_flg, 7 );
			if ( 'acting_sbps_card' == $acting_flg ) {
				if ( 'on' == $acting_opts['card_activate'] && 'auto' == $acting_opts['sales'] ) {
					$status = 'manual';
				} elseif ( 'token' == $acting_opts['card_activate'] && 'auto' == $acting_opts['sales'] ) {
					$status = 'sales';
				} else {
					$status = $acting_opts['sales'];
				}
			} elseif ( 'acting_sbps_paypay' == $acting_flg ) {
				if ( 'auto' == $acting_opts['paypay_sales'] ) {
					$status = 'manual';
				} else {
					$status = $acting_opts['paypay_sales'];
				}
			}
			$result = ( isset( $results['res_result'] ) ) ? $results['res_result'] : '';
			$tracking_id = ( isset( $results['res_tracking_id'] ) ) ? $results['res_tracking_id'] : '';
			if ( ! isset( $results['amount'] ) ) {
				$results['amount'] = usces_crform( $entry['order']['total_full_price'], false, false, 'return', false );
			}
			$this->save_acting_log( $results, $acting, $status, $result, $order_id, $tracking_id );

			if ( 'acting_sbps_card' == $acting_flg ) {
				if ( 'on' == $acting_opts['card_activate'] && 'auto' == $acting_opts['sales'] ) {
					$connection = $this->get_connection();
					$process_date = date( 'Ymd', current_time( 'timestamp' ) ) . '000000';
					$request_date = date( 'YmdHis', current_time( 'timestamp' ) );
					$sps_hashcode = $acting_opts['merchant_id'] . $acting_opts['service_id'] . $tracking_id . $process_date . $request_date . $acting_opts['hash_key'];
					$sps_hashcode = sha1( $sps_hashcode );

					/* 売上要求（自動売上） */
					$request_settlement = '<?xml version="1.0" encoding="Shift_JIS"?>
<sps-api-request id="ST02-00201-101">
	<merchant_id>' . $acting_opts['merchant_id'] . '</merchant_id>
	<service_id>' . $acting_opts['service_id'] . '</service_id>
	<tracking_id>' . $tracking_id . '</tracking_id>
	<processing_datetime>' . $process_date . '</processing_datetime>
	<request_date>' . $request_date . '</request_date>
	<sps_hashcode>' . $sps_hashcode . '</sps_hashcode>
</sps-api-request>';
					$xml_settlement = $this->get_xml_response( $connection['api_url'], $request_settlement );
					$response_settlement = $this->xml2assoc( $xml_settlement );
					if ( isset( $response_settlement['res_result'] ) ) {
						if ( ! isset( $response_settlement['amount'] ) ) {
							$response_settlement['amount'] = usces_crform( $entry['order']['total_full_price'], false, false, 'return', false );
						}
						$this->save_acting_log( $response_settlement, 'sbps_card', 'sales', $response_settlement['res_result'], $order_id, $tracking_id );
					}
				}
			} elseif ( 'acting_sbps_paypay' == $acting_flg ) {
				if ( 'auto' == $acting_opts['paypay_sales'] ) {
					$connection = $this->get_connection();
					$request_date = date( 'YmdHis', current_time( 'timestamp' ) );
					$sps_hashcode = $acting_opts['merchant_id'] . $acting_opts['service_id'] . $tracking_id . $request_date . $acting_opts['hash_key'];
					$sps_hashcode = sha1( $sps_hashcode );

					/* 売上要求（自動売上） */
					$request_settlement = '<?xml version="1.0" encoding="Shift_JIS"?>
<sps-api-request id="ST02-00201-311">
	<merchant_id>' . $acting_opts['merchant_id'] . '</merchant_id>
	<service_id>' . $acting_opts['service_id'] . '</service_id>
	<tracking_id>' . $tracking_id . '</tracking_id>
	<request_date>' . $request_date . '</request_date>
	<sps_hashcode>' . $sps_hashcode . '</sps_hashcode>
</sps-api-request>';
					$xml_settlement = $this->get_xml_response( $connection['api_url'], $request_settlement );
					$response_settlement = $this->xml2assoc( $xml_settlement );
					if ( isset( $response_settlement['res_result'] ) ) {
						if ( ! isset( $response_settlement['amount'] ) ) {
							$response_settlement['amount'] = usces_crform( $entry['order']['total_full_price'], false, false, 'return', false );
						}
						$this->save_acting_log( $response_settlement, 'sbps_paypay', 'sales', $response_settlement['res_result'], $order_id, $tracking_id );
					}
				}
			}
		}
	}

	/**
	 * 決済ログ取得
	 *
	 * @param  int    $order_id Order number.
	 * @param  string $tracking_id Tracking ID.
	 * @param  string $result Result.
	 * @return array
	 */
	public function get_acting_log( $order_id = 0, $tracking_id, $result = 'OK' ) {
		global $wpdb;

		if ( empty( $order_id ) ) {
			if ( 'OK' == $result ) {
				$query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}usces_acting_log WHERE `tracking_id` = %s AND `result` IN ( 'OK', 'AC' ) ORDER BY ID DESC, datetime DESC",
					$tracking_id
				);
			} else {
				$query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}usces_acting_log WHERE `tracking_id` = %s ORDER BY ID DESC, datetime DESC",
					$tracking_id
				);
			}
		} else {
			if ( 'OK' == $result ) {
				$query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}usces_acting_log WHERE `order_id` = %d AND `tracking_id` = %s AND `result` IN ( 'OK', 'AC' ) ORDER BY ID DESC, datetime DESC",
					$order_id,
					$tracking_id
				);
			} else {
				$query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}usces_acting_log WHERE `order_id` = %d AND `tracking_id` = %s ORDER BY ID DESC, datetime DESC",
					$order_id,
					$tracking_id
				);
			}
		}
		$log_data = $wpdb->get_results( $query, ARRAY_A );
		return $log_data;
	}

	/**
	 * 決済ログ出力
	 *
	 * @param  string $log Log data.
	 * @param  string $acting Acting type.
	 * @param  string $status Status.
	 * @param  string $result Result.
	 * @param  int    $order_id Order number.
	 * @param  string $tracking_id Tracking ID.
	 * @return array
	 */
	private function save_acting_log( $log, $acting, $status, $result, $order_id, $tracking_id ) {
		global $wpdb;

		if ( isset( $log['amount'] ) ) {
			$amount = $log['amount'];
		} elseif ( isset( $log['pay_option_manage']['amount'] ) ) {
			$amount = $log['pay_option_manage']['amount'];
		} elseif ( isset( $log['pay_option_manage']['rec_amount'] ) ) {
			$amount = $log['pay_option_manage']['rec_amount'];
		} else {
			$amount = 0;
		}
		$query = $wpdb->prepare(
			"INSERT INTO {$wpdb->prefix}usces_acting_log ( `datetime`, `log`, `acting`, `status`, `result`, `amount`, `order_id`, `tracking_id` ) VALUES ( %s, %s, %s, %s, %s, %f, %d, %s )",
			current_time( 'mysql' ),
			usces_serialize( $log ),
			$acting,
			$status,
			$result,
			$amount,
			$order_id,
			$tracking_id
		);
		$res = $wpdb->query( $query );
		return $res;
	}

	/**
	 * 最新処理取得
	 *
	 * @param  int    $order_id Order number.
	 * @param  string $tracking_id Tracking ID.
	 * @param  string $result Result.
	 * @return array
	 */
	public function get_acting_latest_log( $order_id, $tracking_id, $result = 'OK' ) {
		$latest_log = array();
		$log_data = $this->get_acting_log( $order_id, $tracking_id, $result );
		if ( $log_data ) {
			$data = current( $log_data );
			$latest_log['acting'] = $data['acting'];
			$latest_log['status'] = $data['status'];
			$latest_log['result'] = $data['result'];
			$latest_log['log'] = usces_unserialize( $data['log'] );
			// if ( isset( $latest_log['log']['amount'] ) ) {
			// 	$latest_log['amount'] = $latest_log['log']['amount'];
			// } elseif ( isset( $latest_log['log']['pay_option_manage']['amount'] ) ) {
			// 	$latest_log['amount'] = $latest_log['log']['pay_option_manage']['amount'];
			// } elseif ( isset( $latest_log['log']['pay_option_manage']['rec_amount'] ) ) {
			// 	$latest_log['amount'] = $latest_log['log']['pay_option_manage']['rec_amount'];
			// } else {
				$latest_log['amount'] = $data['amount'];
			// }
			$latest_log['order_id'] = $data['order_id'];
		}
		return $latest_log;
	}

	/**
	 * 最新決済金額取得
	 *
	 * @param  int    $order_id Order number.
	 * @param  string $tracking_id Tracking ID.
	 * @return int    $amount Sales amount.
	 */
	private function get_sales_amount( $order_id, $tracking_id ) {
		$sales_amount = 0;
		$log_data = $this->get_acting_log( $order_id, $tracking_id );
		if ( $log_data ) {
			$amount = 0;
			$refund = 0;
			foreach ( (array) $log_data as $data ) {
				if ( 'refund' == $data['status'] ) {
					$refund = $data['amount'];
				} else {
					if ( $amount < $data['amount'] ) {
						$amount = $data['amount'];
					}
					if ( 'sales' == $data['status'] ) {
						$sales_amount = $data['amount'];
					}
				}
			}
			if ( 0 == $sales_amount ) {
				$sales_amount = $amount;
			}
			if ( 0 != $refund ) {
				$sales_amount += $refund;
			}
		}
		return $sales_amount;
	}

	/**
	 * 決済処理取得
	 *
	 * @param  int    $order_id Order number.
	 * @param  string $tracking_id Tracking ID.
	 * @return string
	 */
	private function get_acting_status( $order_id, $tracking_id ) {
		global $wpdb;

		$acting_status = '';
		$latest_log = $this->get_acting_latest_log( $order_id, $tracking_id );
		if ( isset( $latest_log['status'] ) ) {
			$acting_status = $latest_log['status'];
		}
		return $acting_status;
	}

	/**
	 * ステータスチェック
	 *
	 * @param  array  $status Status code.
	 * @param  int    $order_id Order number.
	 * @param  string $tracking_id Tracking ID.
	 * @return boolean
	 */
	private function is_status( $status, $order_id, $tracking_id ) {
		$exist = false;
		$log_data = $this->get_acting_log( $order_id, $tracking_id );
		if ( $log_data ) {
			foreach ( (array) $log_data as $data ) {
				if ( in_array( $data['status'], $status ) ) {
					$exist = true;
					break;
				}
			}
		}
		return $exist;
	}

	/**
	 * 決済履歴
	 *
	 * @param  int    $order_id Order number.
	 * @param  string $tracking_id Tracking ID.
	 * @return string
	 */
	private function settlement_history( $order_id, $tracking_id ) {
		$html = '';
		$log_data = $this->get_acting_log( $order_id, $tracking_id, 'ALL' );
		if ( $log_data ) {
			$num = count( $log_data );
			$html = '<table class="settlement-history">
				<thead class="settlement-history-head">
					<tr><th></th><th>' . __( 'Processing date', 'usces' ) . '</th><th>' . __( 'Sequence number', 'usces' ) . '</th><th>' . __( 'Processing classification', 'usces' ) . '</th><th>' . __( 'Amount', 'usces' ) . '</th><th>' . __( 'Result', 'usces' ) . '</th></tr>
				</thead>
				<tbody class="settlement-history-body">';
			foreach ( (array) $log_data as $data ) {
				$log = usces_unserialize( $data['log'] );
				if ( 'NG' == $data['result'] ) {
					$err_code = ( isset( $log['res_err_code'] ) ) ? '<br>' . $log['res_err_code'] : '';
					$class = ' error';
				} else {
					$err_code = '';
					$class = '';
				}
				if ( isset( $log['res_sps_transaction_id'] ) ) {
					$transactionid = $log['res_sps_transaction_id'];
				} elseif ( isset( $log['sps_transaction_id'] ) ) {
					$transactionid = $log['sps_transaction_id'];
				} else {
					$transactionid = '';
				}
				$status_name = ( isset( $data['status'] ) ) ? $this->get_status_name( $data['status'] ) : '';
				$amount = ( isset( $log['amount'] ) ) ? usces_crform( $log['amount'], false, true, 'return', true ) : '';
				if ( isset( $log['amount'] ) ) {
					$amount = usces_crform( $log['amount'], false, true, 'return', true );
				// } elseif ( isset( $log['pay_option_manage']['amount'] ) ) {
				// 	$amount = usces_crform( $log['pay_option_manage']['amount'], false, true, 'return', true );
				// } elseif ( isset( $log['pay_option_manage']['rec_amount'] ) ) {
				// 	$amount = usces_crform( $log['pay_option_manage']['rec_amount'], false, true, 'return', true );
				} else {
					$amount = '';
				}
				$html .= '<tr>
					<td class="num">' . $num . '</td>
					<td class="datetime">' . $data['datetime'] . '</td>
					<td class="transactionid">' . $transactionid . '</td>
					<td class="status">' . $status_name . '</td>
					<td class="amount">' . $amount . '</td>
					<td class="result' . $class . '">' . $data['result'] . $err_code . '</td>
				</tr>';
				$num--;
			}
			$html .= '</tbody>
				</table>';
		}
		return $html;
	}

	/**
	 * 処理区分名称取得
	 *
	 * @param  string $status Status code.
	 * @return string
	 */
	private function get_status_name( $status ) {
		$status_name = '';
		switch ( $status ) {
			case 'manual':/* 指定売上時 */
				$status_name = __( '与信済', 'usces' );
				break;
			case 'auto':/* 自動売上時 */
				$status_name = __( '自動売上', 'usces' );
				break;
			case 'sales':/* 管理画面からの売上要求実行時 */
				$status_name = __( '売上確定', 'usces' );
				break;
			case 'refund':/* 管理画面からの部分返金処理実行時 */
				$status_name = __( '部分返金', 'usces' );
				break;
			case 'increase':/* 増額売上確定通知受信時 */
				$status_name = __( '増額売上確定', 'usces' );
				break;
			case 'pending':/* 管理画面からの増額売上実行後 */
				$status_name = __( '増額売上処理中', 'usces' );
				break;
			case 'expired':/* 増額売上期限切れ */
				$status_name = __( '増額売上期限切れ', 'usces' );
				break;
			case 'cancel':/* 取消 */
				$status_name = __( '取消', 'usces' );
				break;
			case 'error':
				$status_name = __( '決済処理不可', 'usces' );
				break;
			default:
				$status_name = $status;
		}
		return $status_name;
	}

	/**
	 * 決済結果参照
	 *
	 * @param  string $acting Acting type.
	 * @param  int    $order_id Order number.
	 * @param  string $tracking_id Tracking ID.
	 * @return array
	 */
	private function get_settlement_status( $acting, $order_id, $tracking_id ) {
		$acting_opts = $this->get_acting_settings();
		if ( empty( $acting_opts['3des_key'] ) || empty( $acting_opts['3desinit_key'] ) ) {
			return array( 'res_result' => 'NG' );
		}
		$connection = $this->get_connection();
		$encrypted_flg = '1';
		$request_date = date( 'YmdHis', current_time( 'timestamp' ) );
		$sps_hashcode = $acting_opts['merchant_id'] . $acting_opts['service_id'] . $tracking_id . $encrypted_flg . $request_date . $acting_opts['hash_key'];
		$sps_hashcode = sha1( $sps_hashcode );
		switch ( $acting ) {
			case 'sbps_card':
				$api_id = 'MG01-00101-101';
				break;
			case 'sbps_paypay':
				$api_id = 'MG01-00101-311';
				break;
			default:
				$api_id = '';
		}

		$response_settlement = array();
		if ( $api_id ) {
			/* 決済結果参照要求 */
			$request_settlement = '<?xml version="1.0" encoding="Shift_JIS"?>
<sps-api-request id="' . $api_id . '">
	<merchant_id>' . $acting_opts['merchant_id'] . '</merchant_id>
	<service_id>' . $acting_opts['service_id'] . '</service_id>
	<tracking_id>' . $tracking_id . '</tracking_id>
	<encrypted_flg>' . $encrypted_flg . '</encrypted_flg>
	<request_date>' . $request_date . '</request_date>
	<sps_hashcode>' . $sps_hashcode . '</sps_hashcode>
</sps-api-request>';
			$xml_settlement = $this->get_xml_response( $connection['api_url'], $request_settlement );
			$response_settlement = $this->xml2assoc( $xml_settlement, $encrypted_flg );
		}
		return $response_settlement;
	}
}
