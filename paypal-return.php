<?php

/*
Plugin Name: Paypal IPN Module
Plugin URI: http://example.com
Description: Collect Return Data from paypal...
Author: Shimion B.
Version: 1.0
Author URI: http://shimion.com
*/
new PayalReturnHandler();
class PayalReturnHandler{
    private $db ;
    private $wp_query;
    private $user_id ;
    private $post_id ;  
    private $paypal_mode;
    private $paypal_domain;
    private $paypal_email;
    private $mail = array() ;
    private $dboug_mail;
    private $item_name;
    private $item_amount;
    private $return_url;
    private $cancel_url;
    private $notify_url;
    
    public function __construct() {
        //if(is_admin()) return false;
          global $wpdb, $current_user, $wp_query, $mb_type;
         $this->db = $wpdb;
        $this->dboug_mail = 'shimionson@gmail.com';
        $this->paypal_email = 'bertmsx@gmail.com';
        add_action( 'wp_ajax_nopriv_app_paypal_ipn', array($this, 'handle_paypal_return')); // Send Paypal to IPN function 
        add_action( 'wp_ajax_app_paypal_ipn', array($this, 'handle_paypal_return')); 
        
         add_action( 'wp_ajax_nopriv_SendTOPaypal', array($this, 'SendTOPaypal')); // Send Paypal to IPN function 
        add_action( 'wp_ajax_SendTOPaypal', array($this, 'SendTOPaypal')); 
        $this->item_name = $_POST["item_name"];
        $this->item_amount = $_POST["amount"];
        $this->return_url = 'http://www.ihalolove.com/payment-success/';
        $this->cancel_url = 'http://www.ihalolove.com/payment-cancel/';
        $this->notify_url = $_POST["notify_url"];;
        
        $this->paypal_mode = 'live';
        if ($this->paypal_mode == 'live') {
				$this->paypal_domain = 'https://www.paypal.com';
			}else{
				$this->paypal_domain = 'https://www.sandbox.paypal.com';
        }
        
        
        
        
        //echo 'Welcome';
    
    }
        
    
    public function handle_paypal_return() {
		// can check the result of the data using this mail function
        //$mail =  wp_mail($this->dboug_mail, 'Deboug-Post', json_encode($_POST));

        if($_POST['payment_status']== 'Completed'){
             $custom = $_POST['custom'];
             global $wpdb;
            
            $result = $wpdb->update( 
                    'wp_class_order', 
                    array( 
                        'status' => $_POST['payment_status'],	// string
                        'transaction_id' => $_POST['txn_id']	// integer (number) 
                    ), 
                    array( 'ID' => $custom ), 
                    array( 
                        '%s',	// value1
                        '%s'	// value2
                    ), 
                    array( '%d' ) 
                );
            
            
            if($result !== false){
             wp_mail($this->dboug_mail, 'Deboug-Update-Data', $result);
            } 
                   
        }
        
        
        
        // "switch"  does not do anything at this moment but usefull for multiple condition
			switch ($_POST['payment_status']) {
				case 'Partially-Refunded':
					break;

				case 'In-Progress':
					break;

				case 'Completed':
                    
                    
                    
                    
                    break;
				case 'Processed':
					
				case 'Reversed':
					

				case 'Refunded':
					

				case 'Denied':
					

					break;

				case 'Pending':
					

					break;

				default:
					// case: various error cases
			}
		
		exit;
	}
    
    
    
    // use echo admin_url('admin-ajax.php?action=SendTOPaypal') on paypaf form action set action=""
    // It helps to get submitted the form without ajax implementation
    public function SendTOPaypal(){
            
         //   echo 'Welcome';
        
        	// Firstly Append paypal account to querystring
            $querystring .= "?business=".urlencode($this->paypal_email)."&";

            // Append amount& currency (£) to quersytring so it cannot be edited in html

            //The item name and amount can be brought in dynamically by querying the $_POST['item_number'] variable.
            $querystring .= "item_name=".urlencode($this->item_name)."&";
            $querystring .= "amount=".urlencode($this->item_amount)."&";

            //loop for posted values and append to querystring
            foreach($_POST as $key => $value){
                $value = urlencode(stripslashes($value));
                $querystring .= "$key=$value&";
            }
	
            // Append paypal return addresses
            $querystring .= "return=".urlencode(stripslashes($this->return_url))."&";
            $querystring .= "cancel_return=".urlencode(stripslashes($this->cancel_url))."&";
            $querystring .= "notify_url=".urlencode($this->notify_url);

            // Append querystring with custom field
            //$querystring .= "&custom=".USERID;

            // Redirect to paypal IPN
            header('location:'.$this->paypal_domain.'/cgi-bin/webscr'.$querystring);
            exit();
            
    
    }
 
}


