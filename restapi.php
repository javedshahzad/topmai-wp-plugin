<?php
 header( 'Access-Control-Allow-Headers: Authorization, X-WP-Nonce,Content-Type, X-Requested-With');
 header("Access-Control-Allow-Origin: *"); 
 header("Access-Control-Expose-Headers: Content-Length, X-JSON");
 header("Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS");
 header("Access-Control-Allow-Headers: *");     
 header( 'Access-Control-Allow-Credentials: true' );
    /*------- Register rest route for ShineWash rest api --------*/
    add_action('rest_api_init', 'rest_api_routes');
function rest_api_routes(){
	      register_rest_route( 'v2', 'javed', array(
            'methods' => 'post',
            'callback' => 'get_results',
        ));
         register_rest_route('v2', 'login', [
        'methods' => 'post',
        'callback' => 'get_authentication',
    ]);

      register_rest_route('v2', 'signup', [
        'methods' => 'post',
        'callback' => 'add_register_data',
    ]);
      register_rest_route('v2', 'addvideo', [
        'methods' => 'post',
        'callback' => 'add_new_video',
    ]);
         register_rest_route('v2', 'allvideos', [
        'methods' => 'post',
        'callback' => 'get_all_videos',
    ]);
        register_rest_route('v2', 'addnewproduct', [
        'methods' => 'post',
        'callback' => 'add_product',
    ]);
        register_rest_route('v2', 'getallproduct', [
        'methods' => 'post',
        'callback' => 'get_all_products',
    ]);
     register_rest_route('v2', 'bookOrder', [
        'methods' => 'post',
        'callback' => 'add_my_book_order',
    ]);
        register_rest_route('v2', 'myorders', [
        'methods' => 'post',
        'callback' => 'get_all_my_orders',
    ]);

        register_rest_route('v2', 'userProfile', [
        'methods' => 'post',
        'callback' => 'get_user_profile',
        ]);
           register_rest_route('v2', 'changeProfile', [
        'methods' => 'post',
        'callback' => 'get_change_profile',
    ]);

         register_rest_route('v2', 'updateProfile', [
        'methods' => 'post',
        'callback' => 'get_update_profile',
    ]);

           register_rest_route('v2', 'addformpackage', [
        'methods' => 'post',
        'callback' => 'add_new_package',
    ]);



}

/*------- Start functions --------*/








/*------- Authentication --------*/
function get_authentication($request)
{
    $verify_param = verifyRequiredParams([
        'username',
        'password',
        'mobile_token',
    ]);
    if ($verify_param == '1') {
        $username = $request["username"];
        $password = $request["password"];
        $mobile_token = $request["mobile_token"];
        $user = get_user_by('login', $username);
        $user_id = $user->ID;
        $metapassword = get_user_meta($user_id, 'password');

        if ($user_id == '' && $password == '') {
            $response["err"] = true;
            $response['message'] = __("Invalid Credential.");
        } else {
            if ($metapassword[0] != $password) {
                $response["err"] = true;
                $response['message'] = __("Invalid password.");
            } else {
                update_user_meta($user_id, 'mobile_token', $mobile_token);
                $response["err"] = false;
                $response['data'] = $user_id;
            }
        }
        return rest_ensure_response($response);
        wp_die();
    } else {
        echo $verify_param;
        exit();
    }
}

/*------- user registration --------*/
function add_register_data()
{
    global $wpdb;
    $verify_param = verifyRequiredParams([
        'user_name',
        'email',
        'phone',
        'password',
        'mobile_token',
    ]);
    if ($verify_param == '1') {
        $table_name = $wpdb->prefix . "users";
        $getfname = sanitize_text_field($_REQUEST['user_name']);
        $getemail = sanitize_text_field($_REQUEST['email']);
        $getphone = sanitize_text_field($_REQUEST['phone']);
        $getpass = sanitize_text_field($_REQUEST['password']);
        $mobile_token = sanitize_text_field($_REQUEST['mobile_token']);
        $base64_img = sanitize_text_field($_REQUEST['profile']);

        $Iname = uniqid();
        $upload_dir = wp_upload_dir();
        $upload_path =
            str_replace('/', DIRECTORY_SEPARATOR, $upload_dir['path']) .
            DIRECTORY_SEPARATOR;
        $img = str_replace('data:image/png;base64,', '', $base64_img);
        $decoded = base64_decode($img);
        $filename = $Iname . '.png';
        $file_type = 'image/png';
        $hashed_filename = md5($filename . microtime()) . '_' . $filename;
        $upload_file = file_put_contents(
            $upload_path . $hashed_filename,
            $decoded
        );
        $attachment = [
            'post_mime_type' => $file_type,
            'post_title' => preg_replace(
                '/\.[^.]+$/',
                '',
                basename($hashed_filename)
            ),
            'post_content' => '',
            'post_status' => 'inherit',
            'guid' => $upload_dir['url'] . '/' . basename($hashed_filename),
        ];
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $attachment_id = wp_insert_attachment(
            $attachment,
            $upload_dir['path'] . '/' . $hashed_filename
        );
        $attachment_data = wp_generate_attachment_metadata(
            $attachment_id,
            $filename
        );

        wp_update_attachment_metadata($attachment_id, $attachment_data);
        $custrow = $wpdb->get_results(
            "SELECT * FROM $table_name where user_email = '$getemail'"
        );
        if (count($custrow) > 0) {
            $response["err"] = true;
            $response['message'] = __("User already exist.");
            return rest_ensure_response($response);
            wp_die();
        } else {
            $otp = generateNumericOTP(4);
            $html = 'Use this OTP ' . $otp . ' as your login code';
            wp_mail($getemail, __('OTP Send', 'text-domain'), $html);
            /*--------- customer  --------*/
            $WP_array = [
                'user_login' => $getemail,
                'user_email' => $getemail,
                'user_pass' => $getpass,
                'display_name' => $getfname,
                'first_name' => $getfname,
                'user_nicename' => $getfname,
                'user_url' => 'Book-My-Schedule',
            ];
            $id = wp_insert_user($WP_array);

            update_user_meta($id, 'user_dob', '');
            update_user_meta($id, 'user_phone', $getphone);
            update_user_meta($id, 'user_otp_varification', $otp);
            update_user_meta($id, 'password', $getpass);
            update_user_meta($id, 'user_varification_status', '0');
            // update_user_meta($id, 'user_profile', $attachment_id);
            update_user_meta($id, 'user_fev_branch', '');
            update_user_meta($id, 'user_fev_branch', '');
            update_user_meta($id, 'mobile_token', $mobile_token);
            // if ($base64_img != "undefined") {
            //    update_user_meta($id, 'user_profile', $attachment_id);
            // }
            wp_set_password($getpass, $id);
            $user_data = get_userdata($id);
            $data = [];
            $obj = new stdclass();
            $obj->ID = $id;
            $obj->name = $getfname;
            $obj->email = $getemail;
            array_push($data, $obj);
            $response["err"] = false;
            $response['data'] = $data;
            return rest_ensure_response($response);
            wp_die();
        }
    } else {
        echo $verify_param;
        exit();
    }
}

/*------- Add new videos --------*/
function add_new_video()
{
    $verify_param = verifyRequiredParams([
        'user_id'
    ]);
    if ($verify_param == '1') {
                global $wpdb;
                $table_name = $wpdb->prefix . "book_my_videos";;
                $user_id = sanitize_text_field($_REQUEST['user_id']);
                $filname_db = $_FILES["video"]["name"];
                $target_dir = wp_upload_dir()['path'] . "/";
                $target_file = $target_dir . basename($filname_db);
                $temp55 = explode("/uploads/", $target_dir);
                $target_dir_short = '/uploads/' . $temp55[1];
                $extension = explode(".", $filname_db);
                $imageName = round(microtime(true)) . '.' . end($extension);
                $movefiles = move_uploaded_file($_FILES["video"]["tmp_name"],$target_dir.$imageName);
                $filename11 = $target_dir_short . $imageName;
                if ($movefiles) {
                   $filename11 = $target_dir_short . $imageName;
                $upd2 = $wpdb->query("INSERT INTO $table_name (`user_id`,`video`,`created_at`) VALUES ('$user_id','$filename11',current_timestamp());");
                $response["err"] = false;
                $response['message'] = __("Video Uploaded Successfull");
                } else {
                $response["err"] = true;
                $response['message'] = __("Error! Please Fill all Details");
              }
        return rest_ensure_response($response);
        wp_die();
    } else {
        echo $verify_param;
        exit();
    }
}
/*------- Add mew product --------*/
function add_product()
{
    $verify_param = verifyRequiredParams([
        'product_name',
        'product_title',
        'price'

    ]);
    if ($verify_param == '1') {
                global $wpdb;
                $table_name = $wpdb->prefix . "book_my_products";;
                $user_id = sanitize_text_field($_REQUEST['user_id']);
                $product_name = sanitize_text_field($_REQUEST['product_name']);
                $product_title = sanitize_text_field($_REQUEST['product_title']);
                $product_description = sanitize_text_field($_REQUEST['product_description']);
                $product_category = sanitize_text_field($_REQUEST['product_category']);
                $status = sanitize_text_field($_REQUEST['status']);
                $price = sanitize_text_field($_REQUEST['price']);
                 $product_weight = sanitize_text_field($_REQUEST['product_weight']);

                $filname_db = $_FILES["product_image"]["name"];
                $target_dir = wp_upload_dir()['path'] . "/";
                $target_file = $target_dir . basename($filname_db);
                $temp55 = explode("/uploads/", $target_dir);
                $target_dir_short = '/uploads/' . $temp55[1];
                $extension = explode(".", $filname_db);
                $imageName = round(microtime(true)) . '.' . end($extension);
        $movefiles = move_uploaded_file($_FILES["product_image"]["tmp_name"],$target_dir.$imageName);
          $filename11 = $target_dir_short . $imageName;
            if ($movefiles) {
                   $filenameinsert = $target_dir_short . $imageName;
                    $addproduct = $wpdb->insert($table_name, [
                    'user_id' => $user_id,
                    'product_name' => $product_name,
                    'product_title' => $product_title,
                    'product_description' => $product_description,
                    'product_category' => $product_category,
                    'status' => $status,
                    'product_image' => $filenameinsert,
                    'price' => $price,
                    'product_weight' => $product_weight
                    
                    ]);

            $response["err"] = false;
            $response['message'] = __("Video Uploaded Successfull");
            } else {
            $response["err"] = true;
            $response['message'] = __("Error! Please check details");
              }
        return rest_ensure_response($response);
        wp_die();
    } else {
        echo $verify_param;
        exit();
    }
}




/*------- Book Order--------*/
function add_my_book_order()
{
    $verify_param = verifyRequiredParams([
        'product_name',
        'product_title',
        'price'

    ]);
    if ($verify_param == '1') {
                global $wpdb;
                $table_name = $wpdb->prefix . "book_my_orders";
                $user_id = sanitize_text_field($_REQUEST['user_id']);

                $from_name_user = sanitize_text_field($_REQUEST['from_name_user']);
                $from_company = sanitize_text_field($_REQUEST['from_company']);
                $from_email = sanitize_text_field($_REQUEST['from_email']);
                $from_phone = sanitize_text_field($_REQUEST['from_phone']);
                $from_country = sanitize_text_field($_REQUEST['from_country']);
                $from_postal_code = sanitize_text_field($_REQUEST['from_postal_code']);
                $from_address = sanitize_text_field($_REQUEST['from_address']);
                $from_city = sanitize_text_field($_REQUEST['from_city']);
                $from_state = sanitize_text_field($_REQUEST['from_state']);

                $to_name_user = sanitize_text_field($_REQUEST['to_name_user']);
                $to_company = sanitize_text_field($_REQUEST['to_company']);
                $to_email = sanitize_text_field($_REQUEST['to_email']);
                $to_phone = sanitize_text_field($_REQUEST['to_phone']);
                $to_country = sanitize_text_field($_REQUEST['to_country']);
                $to_postal_code = sanitize_text_field($_REQUEST['to_postal_code']);
                $to_address = sanitize_text_field($_REQUEST['to_address']);
                $to_city = sanitize_text_field($_REQUEST['to_city']);
                $to_state = sanitize_text_field($_REQUEST['to_state']);

                $product_name = sanitize_text_field($_REQUEST['product_name']);
                $product_title = sanitize_text_field($_REQUEST['product_title']);
                $product_description = sanitize_text_field($_REQUEST['product_description']);
                $product_category = sanitize_text_field($_REQUEST['product_category']);
                $status = sanitize_text_field($_REQUEST['status']);
                $price = sanitize_text_field($_REQUEST['price']);
                $product_weight = sanitize_text_field($_REQUEST['product_weight']);
                $product_image = sanitize_text_field($_REQUEST['product_image']);
                $product_id = sanitize_text_field($_REQUEST['product_id']);
                $subtotal_price = sanitize_text_field($_REQUEST['subtotal_price']);
                $final_price = sanitize_text_field($_REQUEST['final_price']);
                $quantity = sanitize_text_field($_REQUEST['quantity']);

        
                    $addorder = $wpdb->insert($table_name, [
                    'user_id' => $user_id,
                    'from_name_user' => $from_name_user,
                    'from_company' => $from_company,
                    'from_email' => $from_email,
                    'from_phone' => $from_phone,
                    'from_country' => $from_country,
                    'from_postal_code' => $from_postal_code,
                    'from_address' => $from_address,
                    'from_city' => $from_city,
                    'from_state' => $from_state,
                    'to_name_user' => $to_name_user,
                    'to_company' => $to_company,
                    'to_email' => $to_email,
                    'to_phone' => $to_phone,
                    'to_country' => $to_country,
                    'to_postal_code' => $to_postal_code,
                    'to_address' => $to_address,
                    'to_city' => $to_city,
                    'to_state' => $to_state,
                    'product_name' => $product_name,
                    'product_title' => $product_title,
                    'product_description' => $product_description,
                    'product_category' => $product_category,
                    'status' => $status,
                    'product_image' => $product_image,
                    'price' => $price,
                    'product_weight' => $product_weight,
                    'product_id' => $product_id,
                    'subtotal_price' => $subtotal_price,
                    'final_price' => $final_price,
                    'quantity' => $quantity
                    
                    ]);
                    if($addorder){
                $insertid = $wpdb->insert_id;
            $response["err"] = false;
            $response['data'] = $insertid;
            } else {
            $response["err"] = true;
            $response['message'] = __("Error! Please check details");
              }
        return rest_ensure_response($response);
        wp_die();
    } else {
        echo $verify_param;
        exit();
    }
}




/*------- Add new package form --------*/
function add_new_package()
{
    $verify_param = verifyRequiredParams([
        'user_id'

    ]);
    if ($verify_param == '1') {
                global $wpdb;
                $table_name = $wpdb->prefix . "book_my_form_package";

                $user_name = sanitize_text_field($_REQUEST['user_name']);
                $length = sanitize_text_field($_REQUEST['length']);
                $broad = sanitize_text_field($_REQUEST['broad']);
                $tall = sanitize_text_field($_REQUEST['tall']);
                $weight = sanitize_text_field($_REQUEST['weight']);
                $user_id = sanitize_text_field($_REQUEST['user_id']);
                $product_id = sanitize_text_field($_REQUEST['product_id']);
                $order_id = sanitize_text_field($_REQUEST['order_id']);

                    $addpackage = $wpdb->insert($table_name, [
                    'user_name' => $user_name,
                    'length' => $length,
                    'broad' => $broad,
                    'tall' => $tall,
                    'weight' => $weight,
                    'user_id' => $user_id,
                    'product_id' => $product_id,
                    'order_id' => $order_id
                    ]);
                    if($addpackage){
                $insertid = $wpdb->insert_id;
            $response["err"] = false;
            $response['data'] = $insertid;
            } else {
            $response["err"] = true;
            $response['message'] = __("Error! Please check details");
              }
        return rest_ensure_response($response);
        wp_die();
    } else {
        echo $verify_param;
        exit();
    }
}



/*------- Get all videos--------*/
function get_all_videos()
{
    global $wpdb;
    $verify_param = verifyRequiredParams(['user_id']);
    if ($verify_param == '1') {
        $table_name = $wpdb->prefix ."book_my_videos";
        $user_id = sanitize_text_field($_POST['user_id']);
        $getservbycat = $wpdb->get_results("SELECT * FROM $table_name");
        if (count((array) $getservbycat) == 0) {
            $response["err"] = true;
            $response['message'] = __("Data not found.");
        } else {
            $service_filter = [];
            foreach ($getservbycat as $service_data) {
                $img_url =site_url() . "/wp-content" . $service_data->video;
                $catservimage = $img_url;
                $service_data->video = $catservimage;
                array_push($service_filter, $service_data);
            }
            $response["err"] = false;
            $response['data'] = $service_filter;
        }
        return rest_ensure_response($response);
        wp_die();
    } else {
        echo $verify_param;
        exit();
    }
}

/*------- Get all products--------*/
function get_all_products()
{
    global $wpdb;
    $verify_param = verifyRequiredParams(['user_id']);
    if ($verify_param == '1') {
        $table_name = $wpdb->prefix ."book_my_products";
        $user_id = sanitize_text_field($_POST['user_id']);
        $getallProduct = $wpdb->get_results("SELECT * FROM $table_name ");
        if (count((array) $getallProduct) == 0) {
            $response["err"] = true;
            $response['message'] = __("Data not found.");
        } else {
           {
            $productarray = [];
            foreach ($getallProduct as $product_data) {
                $img_url =site_url() . "/wp-content" . $product_data->product_image;
                $proservimage = $img_url;
                $product_data->product_image = $proservimage;
                array_push($productarray, $product_data);
            }
            $response["err"] = false;
            $response['data'] = $productarray;
        }
        }
        return rest_ensure_response($response);
        wp_die();
    } else {
        echo $verify_param;
        exit();
    }
}

/*------- Get All orders--------*/
function get_all_my_orders()
{
    global $wpdb;
    $verify_param = verifyRequiredParams(['user_id']);
    if ($verify_param == '1') {
        $table_name = $wpdb->prefix ."book_my_orders";
        $user_id = sanitize_text_field($_POST['user_id']);
        $getallorder = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id='$user_id'");
        if (count((array) $getallorder) == 0) {
            $response["err"] = true;
            $response['message'] = __("Data not found.");
        } else {
            $response["err"] = false;
            $response['data'] = $getallorder;
        }
        return rest_ensure_response($response);
        wp_die();
    } else {
        echo $verify_param;
        exit();
    }
}


/*------- Get user profile--------*/
function get_user_profile()
{
    global $wpdb;
    $verify_param = verifyRequiredParams(['user_id']);
    if ($verify_param == '1') {
        $table_name = $wpdb->prefix . "users";
        $userid = sanitize_text_field($_REQUEST['user_id']);
        $rowuser = $wpdb->get_results(
            "SELECT * FROM $table_name where ID = '$userid'"
        );
        if (count((array) $rowuser) == 0) {
            $response["err"] = true;
            $response['message'] = __("Data not found.");
        } else {
            $user_obj = new stdclass();
            foreach ($rowuser as $rc) {
                $user_obj->user_id = $rc->ID;
                $user_obj->user_name = $rc->display_name;
                $user_obj->email = $rc->user_email;
            }
            $dob = get_user_meta($userid, 'user_dob');
            $uphone = get_user_meta($userid, 'user_phone');
            $userprofile = get_user_meta($userid, 'user_profile');
            if ($userprofile[0] == '') {
                $image = get_avatar_data($userid);
                $user_obj->profile = $image['url'];
            } else {
                $user_obj->profile = wp_get_attachment_url($userprofile[0]);
            }

            $user_obj->dob = $dob[0];
            $user_obj->phone = $uphone[0];
            $response["err"] = false;
            $response['data'] = $user_obj;
        }
        return rest_ensure_response($response);
        wp_die();
    } else {
        echo $verify_param;
        exit();
    }
}


/*------- Upload user profile--------*/
function get_change_profile()
{
    $verify_param = verifyRequiredParams(['user_id', 'profile']);
    if ($verify_param == '1') {
        global $wpdb;
        $table_name = $wpdb->prefix . "users";
        $user_id = sanitize_text_field($_REQUEST['user_id']);
        $base64_img = sanitize_text_field($_REQUEST['profile']);
        $Iname = uniqid();
        $upload_dir = wp_upload_dir();
        $upload_path =
            str_replace('/', DIRECTORY_SEPARATOR, $upload_dir['path']) .
            DIRECTORY_SEPARATOR;
        $img = str_replace('data:image/png;base64,', '', $base64_img);
        $decoded = base64_decode($img);
        $filename = $Iname . '.png';
        $file_type = 'image/png';
        $hashed_filename = md5($filename . microtime()) . '_' . $filename;
        $upload_file = file_put_contents(
            $upload_path . $hashed_filename,
            $decoded
        );
        $attachment = [
            'post_mime_type' => $file_type,
            'post_title' => preg_replace(
                '/\.[^.]+$/',
                '',
                basename($hashed_filename)
            ),
            'post_content' => '',
            'post_status' => 'inherit',
            'guid' => $upload_dir['url'] . '/' . basename($hashed_filename),
        ];
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $attachment_id = wp_insert_attachment(
            $attachment,
            $upload_dir['path'] . '/' . $hashed_filename
        );
        $attachment_data = wp_generate_attachment_metadata(
            $attachment_id,
            $filename
        );
        wp_update_attachment_metadata($attachment_id, $attachment_data);
        if ($attachment_id) {
            $userdob = update_user_meta(
                $user_id,
                'user_profile',
                $attachment_id
            );
            $response["err"] = false;
            $response['data'] = 'successfully upload';
        } else {
            $response["err"] = true;
            $response['message'] = __("Upload error.");
        }
        return rest_ensure_response($response);
        wp_die();
    } else {
        echo $verify_param;
        exit();
    }
}

/*------- Update user profile--------*/
function get_update_profile()
{
    $verify_param = verifyRequiredParams([
        'user_id',
        'user_name',
        'user_email',
        'user_phone',
    ]);
    if ($verify_param == '1') {
        global $wpdb;
        $table_name = $wpdb->prefix . "users";
        $userid = sanitize_text_field($_REQUEST['user_id']);
        $user_name = sanitize_text_field($_REQUEST['user_name']);
        $user_email = sanitize_text_field($_REQUEST['user_email']);
        $user_phone = sanitize_text_field($_REQUEST['user_phone']);
        $user_dob = sanitize_text_field($_REQUEST['dob']);
        $file = $_FILES['profile'];
        $attachment_id = upload_wp_user_file($file);
        if ($attachment_id) {
            $upd = $wpdb->query(
                "update $table_name set display_name = '$user_name', user_email='$user_email',user_login='$user_email' where ID ='$userid'"
            );
        } else {
            $upd = $wpdb->query(
                "update $table_name set display_name = '$user_name', user_email='$user_email',user_login='$user_email' where ID ='$userid'"
            );
        }
        $userdob = update_user_meta($userid, 'user_dob', $user_dob);
        $userphone = update_user_meta($userid, 'user_phone', $user_phone);
        if ($upd > 0 or $userdob == 1 or $userphone == 1) {
            $rowuser = $wpdb->get_results(
                "SELECT * FROM $table_name where ID = '$userid'"
            );

            $user_obj = new stdclass();
            foreach ($rowuser as $rc) {
                $user_obj->user_id = $rc->ID;
                $user_obj->user_name = $rc->display_name;
                $user_obj->email = $rc->user_email;
            }
            $uphone = get_user_meta($userid, 'user_phone');
            $image = get_avatar_data($userid);
            $user_obj->profile = $image['url'];
            $user_obj->dob = $dob[0];
            $user_obj->phone = $uphone[0];
            $response["err"] = false;
            $response['data'] = $user_obj;
        } else {
            $response["err"] = true;
            $response['message'] = __("Update error.");
        }
        return rest_ensure_response($response);
        wp_die();
    } else {
        echo $verify_param;
        exit();
    }
}



function get_results(){

	          $id = sanitize_text_field($_REQUEST['id']);
            $name = sanitize_text_field($_REQUEST['name']);
            $age = sanitize_text_field($_REQUEST['age']);
            $class=sanitize_text_field($_REQUEST['class']);
            if ( $id == '' || $name == '' || $age == '' || $class == '') {
            	echo "id,name,age,class are required";
            }else{
            global $wpdb;
            $table_name = $wpdb->prefix . "users";
             $data = $wpdb->get_results("SELECT * FROM $table_name");
             if ($data > 0) {
              	 $response["err"] = false;
                $response['data'] = $data ;
              } else{
              	 $response["err"] = true;
                $response['data'] ="Errror";
              }

            }
             return rest_ensure_response( $response );
                wp_die();

}


function generateNumericOTP($n)
{
    $generator = "1357902468";
    $result = "";
    for ($i = 1; $i <= $n; $i++) {
        $result .= substr($generator, rand() % strlen($generator), 1);
    }
    return $result;
}


/*------- verify Require Parameter for api request --------*/
function verifyRequiredParams($required_fields)
{
    $error = false;
    $error_fields = "";
    $request_params = [];
    $request_params = $_REQUEST;
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    }
    foreach ($required_fields as $field) {
        if (
            !isset($request_params[$field]) ||
            strlen(trim($request_params[$field])) <= 0
        ) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        $response = [];
        $response["err"] = true;
        $response["data"] =
            'Required fields ' .
            substr($error_fields, 0, -2) .
            ' is missing or empty';
        return json_encode($response);
    } else {
        return 1;
    }
}


//upload image
function upload_wp_user_file($file)
{
    require_once ABSPATH . 'wp-admin/includes/admin.php';
    $file_return = wp_handle_upload($file, [
        'test_form' => false,
    ]);
    if (
        isset($file_return['error']) ||
        isset($file_return['upload_error_handler'])
    ) {
        return false;
    } else {
        $filename = $file_return['file'];
        $attachment = [
            'post_mime_type' => $file_return['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
            'post_content' => '',
            'post_status' => 'inherit',
            'guid' => $file_return['url'],
        ];
        $attachment_id = wp_insert_attachment($attachment, $file_return['url']);

        require_once ABSPATH . 'wp-admin/includes/image.php';
        $attachment_data = wp_generate_attachment_metadata(
            $attachment_id,
            $filename
        );
        wp_update_attachment_metadata($attachment_id, $attachment_data);

        if (0 < intval($attachment_id)) {
            return $attachment_id;
        }
    }
    return false;
}


?>