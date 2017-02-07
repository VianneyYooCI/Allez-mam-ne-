<?php
add_action('wp_ajax_add_click_event_to_user','add_click_event_to_user');
add_action('wp_ajax_nopriv_add_click_event_to_user','add_click_event_to_user');
function add_click_event_to_user(){
    $userId = get_current_user_id();
    $currentClick = intval(get_user_meta($userId,"click_profile_or_society", true));
    update_user_meta($userId, 'click_profile_or_society', ++$currentClick);
    if($currentClick === 20){
        $keywords = array(
            "targetFirstName" => get_user_by("id", $userId)->user_firstname,
            "targetLastName" => get_user_by("id", $userId)->user_lastname
        );
        foreach(array(FLORENCE_HANCZAKOWSKI_EMAIL, PAULINE_EMAIL, SECRETARIAT_EMAIL) as $userEmail){
            $keywords["firstName"] = get_user_by("email", $userEmail)->user_firstname;
            $keywords["lastName"] = get_user_by("email", $userEmail)->user_lastname;
            Mail::sendEmail($userEmail, "Consultation abusive de l'annuaire",
                "30-clickTwentyTimesOnSocietiesOrUserProfiles", $keywords);
        }
    }
    exit;
}

add_action('wp_ajax_load_list_con','load_list_con');
add_action('wp_ajax_nopriv_load_list_con','load_list_con');
function load_list_con(){
	$page=(isset($_POST['page']))?'page="'.$_POST['page'].'"':'';
	$from=(isset($_POST['from']))?'from="'.$_POST['from'].'"':'';
	$to=(isset($_POST['to']))?'to="'.$_POST['to'].'"':'';
	$cat=(isset($_POST['cat']))?'cat="'.$_POST['cat'].'"':'';
	echo do_shortcode('[list_conferences '.$page.' '.$from.' '.$to.' '.$cat.']');
exit;
}

add_action('wp_ajax_load_list_pub','load_list_pub');
add_action('wp_ajax_nopriv_load_list_pub','load_list_pub');
function load_list_pub(){
	$year=(isset($_POST['select_year']))?'year="'.$_POST['select_year'].'"':'';
	$pri=(isset($_POST['select_pri']))?'pri="'.$_POST['select_pri'].'"':'';
	$lang=(isset($_POST['select_lang']))?'lang="'.$_POST['select_lang'].'"':'';
	$format=(isset($_POST['select_type']))?'format="'.$_POST['select_type'].'"':'';
	$key=($_POST['s_title']<>"")?'key="'.$_POST['s_title'].'"':'';
	$cat=(isset($_POST['cat_pub']))?'cat="'.$_POST['cat_pub'].'"':'';
	echo do_shortcode('[list_publications '.$year.' '.$cat.' '.$pri.' '.$lang.' '.$format.' '.$key.']');
exit;
}

add_action('wp_ajax_load_list_members','load_list_members');
add_action('wp_ajax_nopriv_load_list_members','load_list_members');
function load_list_members(){

    if($_GET['type'] == 'main') {

        $conferencesId = $_GET['conference'];

        $countPlaceMember = get_post_meta($conferencesId, 'place_members', true);
        $usersMain = get_post_meta($conferencesId, 'members_connect_conferences', true);
        $usersWaiting = get_post_meta($conferencesId, 'members_connect_conferences_waiting', true);

        function replace($array)
        {
            return ($array['event_contact_email'] == $_GET['email']);
        }

        $userReplace = array_filter($usersMain, 'replace');
        $usersWaiting[] = array_shift($userReplace);

        function delete($array) {
            return ($array['event_contact_email'] != $_GET['email']);
        }
        $users = array_filter($usersMain, 'delete');
        update_post_meta($conferencesId, 'members_connect_conferences', $users);
        update_post_meta($conferencesId, 'members_connect_conferences_waiting', $usersWaiting);
        $countPlaceMember = $countPlaceMember + 1;
        update_post_meta($conferencesId, 'place_members', $countPlaceMember);
    }

    if($_GET['type'] == 'waiting'){

        $conferencesId = $_GET['conference'];
        $countPlaceMember = get_post_meta($conferencesId, 'place_members', true);
        if($countPlaceMember > 0) {
            $usersMain = get_post_meta($conferencesId, 'members_connect_conferences', true);
            $usersWaiting = get_post_meta($conferencesId, 'members_connect_conferences_waiting', true);
            function replace($array)
            {
                return ($array['event_contact_email'] == $_GET['email']);
            }
            $userReplace = array_filter($usersWaiting, 'replace');
            $usersMain[] = array_shift($userReplace);

            function delete($array)
            {
                return ($array['event_contact_email'] != $_GET['email']);
            }

            $users = array_filter($usersWaiting, 'delete');
            update_post_meta($conferencesId, 'members_connect_conferences_waiting', $users);
            update_post_meta($conferencesId, 'members_connect_conferences', $usersMain);
            $countPlaceMember = $countPlaceMember - 1;
            update_post_meta($conferencesId, 'place_members', $countPlaceMember);
        } else {
            echo 'Il ny a pas de sièges vides lors de la conférence dans la liste principale';
        }
    }
}

add_action('wp_ajax_load_list_users','load_list_users');
add_action('wp_ajax_nopriv_load_list_users','load_list_users');
function load_list_users(){

    $member = array();
    $type = $_GET['type'];
    $email = $_GET['email'];
    $conferencesId = $_GET['conference'];

    if($type == 'main') {
        $countPlaceUsers = get_post_meta($conferencesId, 'place_non_members', true);
        $usersMain = get_post_meta($conferencesId, 'members_non_connect_conferences', true);
        $usersWaiting = get_post_meta($conferencesId, 'members_non_connect_conferences_waiting', true);

        function replace($array)
        {
            return ($array['event_contact_email_non'] == $_GET['email']);
        }

        $userReplace = array_filter($usersMain, 'replace');
        $usersWaiting[] = array_shift($userReplace);

        function delete($array) {
            return ($array['event_contact_email_non'] != $_GET['email']);
        }
        $users = array_filter($usersMain, 'delete');
        update_post_meta($conferencesId, 'members_non_connect_conferences', $users);
        update_post_meta($conferencesId, 'members_non_connect_conferences_waiting', $usersWaiting);
        $countPlaceUsers = $countPlaceUsers + 1;
        update_post_meta($conferencesId, 'place_non_members', $countPlaceUsers);
    }

    if($type == 'waiting'){
        $countPlaceUsers = get_post_meta($conferencesId, 'place_non_members', true);
        if($countPlaceUsers > 0) {
            $usersWaiting = get_post_meta($conferencesId, 'members_non_connect_conferences_waiting', true);
            $usersMain = get_post_meta($conferencesId, 'members_non_connect_conferences', true);

            function replace($array)
            {
                return ($array['event_contact_email_non'] == $_GET['email']);
            }
            $userReplace = array_filter($usersWaiting, 'replace');
            $usersMain[] = array_shift($userReplace);


            function delete($array)
            {
                return ($array['event_contact_email_non'] != $_GET['email']);
            }

            $users = array_filter($usersWaiting, 'delete');
            update_post_meta($conferencesId, 'members_non_connect_conferences_waiting', $users);
            update_post_meta($conferencesId, 'members_non_connect_conferences', $usersMain);
            $countPlaceUsers = $countPlaceUsers - 1;
            update_post_meta($conferencesId, 'place_non_members', $countPlaceUsers);
        } else {
            echo 'Il ny a pas de sièges vides lors de la conférence dans la liste principale0';
        }
    }
}

add_action('wp_ajax_load_param_block','load_param_block');
add_action('wp_ajax_nopriv_load_param_block','load_param_block');
function load_param_block(){

    global $wpdb;

    $wpdb->update( 'cl59rb_clusif_lockouts',
        array( 'ipIsBlocked_clusif_lockouts' => $_POST['block'] ),
        array( 'id_clusif_lockouts' => $_POST['id'] ),
        array( '%s' ),
        array( '%s' )
    );
}

add_action('wp_ajax_load_param_doc','load_param_doc');
add_action('wp_ajax_nopriv_load_param_doc','load_param_doc');
function load_param_doc(){

    if(!is_user_logged_in()){
        wp_redirect(home_url().'/login');
        exit;
    }

    global $wpdb;

    $usersEditDoc = Document::isModifierUserDocNow($_POST['register']);

    if($usersEditDoc != null && $usersEditDoc != ''){
        $usersEditDoc =  json_decode($usersEditDoc[0]->last_edit_user_date);
    }

    if(count($usersEditDoc)){
        if($usersEditDoc->userId != get_current_user_id() && ($usersEditDoc->date_modifier + 30) > strtotime(date('Y-m-d H:i:s'))){
            $success = false;
        } else {
            $success = true;
        }
        $usersEditDoc = array();
        $usersEditDoc['userId'] = get_current_user_id();
        $usersEditDoc['date_modifier'] = strtotime(date('Y-m-d H:i:s'));
    } else {
        $usersEditDoc = array();
        $usersEditDoc['userId'] = get_current_user_id();
        $usersEditDoc['date_modifier'] = strtotime(date('Y-m-d H:i:s'));
        $success = true;
    }

    $usersEditDoc = json_encode($usersEditDoc);

    $wpdb->update( 'cl59rb_clusif_doc',
        array( 'last_edit_user_date' => $usersEditDoc ),
        array( 'register_clusif_doc' => $_POST['register'] ),
        array( '%s' ),
        array( '%d' )
    );

    $success = json_encode($success);
    echo ($success);
    exit;
}

add_action('wp_ajax_load_list_event','load_list_event');
add_action('wp_ajax_nopriv_load_list_event','load_list_event');
function load_list_event(){
    $param = '';
    if(isset($_POST['workgroup']) && $_POST['workgroup'] != '' && isset($_POST['category']) && $_POST['category'] != ''){
        $param  = $_POST['workgroup'].",".$_POST['category'];
    } elseif(isset($_POST['workgroup']) && $_POST['workgroup'] != ''){
        $param  = $_POST['workgroup'];
    } elseif(isset($_POST['category']) && $_POST['category'] != ''){
        $param  = $_POST['category'];
    }

    if($param != ''){ $events = Event::findByParam($param); } else {
        $events = Event::findAll();
    }

    if(isset($_POST['month']) && $_POST['month'] != '' && $param != ''){
        $date = $_POST['month'];
        $events = Event::findByMonthCategory($param, $date);
    }

    if(isset($_POST['month']) && $_POST['month'] != '' && $param == ''){
        $date = $_POST['month'];
        $events = Event::findByMonth($date);
    }

    if(isset($_POST['week']) && $_POST['week'] != '' && $param == ''){
        $date = $_POST['week'];
        $events = Event::findByWeek($date);
    }

    for($i = 0; $i < count($events); $i++) {
        if(isset($events[$i]->data)) {
            $events[$i]->place = get_post_meta($events[$i]->data->ID, 'place', true);
        } else {
            $events[$i]->place = get_post_meta($events[$i]->ID, 'place', true);
        }
        if(isset($events[$i]->data)) {
            $events[$i]->date = date('Y-m-d', strtotime(get_post_meta($events[$i]->data->ID, 'date', true)));
        } else {
            $events[$i]->date = date('Y-m-d', strtotime(get_post_meta($events[$i]->ID, 'date', true)));
        }
        if(isset($events[$i]->data)) {
            $events[$i]->month = date('M', strtotime(get_post_meta($events[$i]->data->ID, 'date', true)));
        } else {
            $events[$i]->month = date('M', strtotime(get_post_meta($events[$i]->ID, 'date', true)));
        }
        if(isset($events[$i]->data)) {
            $events[$i]->timeFrom= get_post_meta($events[$i]->data->ID, 'timeFrom', true);
        } else {
            $events[$i]->timeFrom= get_post_meta($events[$i]->ID, 'timeFrom', true);
        }
        if(isset($events[$i]->data)) {
            $events[$i]->timeTo = get_post_meta($events[$i]->data->ID, 'timeTo', true);
        } else {
            $events[$i]->timeTo = get_post_meta($events[$i]->ID, 'timeTo', true);
        }
        if(isset($events[$i]->data)) {
            if (!is_numeric(get_post_meta($events[$i]->data->ID, 'category', true)) ||
                get_post(get_post_meta($events[$i]->data->ID, 'category', true)) == null) {
                $events[$i]->category = get_post_meta($events[$i]->data->ID, 'category', true);
            } else {
                $events[$i]->category = get_post(get_post_meta($events[$i]->data->ID, 'category', true))->post_title;
            }
        } else {
            if (!is_numeric(get_post_meta($events[$i]->ID, 'category', true)) ||
                get_post(get_post_meta($events[$i]->ID, 'category', true)) == null) {
                $events[$i]->category = get_post_meta($events[$i]->ID, 'category', true);
            } else {
                $events[$i]->category = get_post(get_post_meta($events[$i]->ID, 'category', true))->post_title;
            }
        }
    }
    $events = json_encode($events);
    echo ($events);
    exit;
}

add_action('wp_ajax_load_list_media','load_list_media');
add_action('wp_ajax_nopriv_load_list_media','load_list_media');
function load_list_media(){
	$cat=(isset($_POST['cat_media']))?'cat="'.substr($_POST['cat_media'],0,-1).'"':'';
	$from=(isset($_POST['from_date']))?'from="'.$_POST['from_date'].'"':'';
	$to=(isset($_POST['to_date']))?'to="'.$_POST['to_date'].'"':'';
	$key=($_POST['key']<>"")?'key="'.$_POST['key'].'"':'';
	$page=(isset($_GET['page']))?'page="'.$_GET['page'].'"':'';
	echo do_shortcode('[list_media '.$cat.' '.$from.' '.$to.' '.$key.' '.$page.']');
exit;
}

add_action('wp_ajax_load_list_glossaire','load_list_glossaire');
add_action('wp_ajax_nopriv_load_list_glossaire','load_list_glossaire');
function load_list_glossaire(){
	echo do_shortcode('[list_glossaire lettre="'.$_POST['lettre'].'"]');
exit;
}

add_action('wp_ajax_download_publication_event','download_publication_event');
add_action('wp_ajax_nopriv_download_publication_event','download_publication_event');
function download_publication_event(){
    $postId = $_POST["postId"];
    $currYear = date("Y");
    $downloads = get_post_meta($postId, 'download_statistics', true);
    $downloads = $downloads ? $downloads : array();
    $downloads[$currYear] = array_key_exists($currYear, $downloads) ? intval($downloads[$currYear]) + 1 : 1;
    update_post_meta($postId, 'download_statistics', $downloads);
    exit;
}

add_action('wp_ajax_print_publication_event','print_publication_event');
add_action('wp_ajax_nopriv_print_publication_event','print_publication_event');
function print_publication_event(){
    $postId = $_POST["postId"];
    $currYear = date("Y");
    $prints = get_post_meta($postId, 'print_statistics', true);
    $prints = $prints ? $prints : array();
    $prints[$currYear] = array_key_exists($currYear, $prints) ? intval($prints[$currYear]) + 1 : 1;
    update_post_meta($postId, 'print_statistics', $prints);
    exit;
}

add_action('wp_ajax_save_guest_publication','save_guest_publication');
add_action('wp_ajax_nopriv_save_guest_publication','save_guest_publication');
function save_guest_publication(){
    $postId = $_POST["postId"];
    $guests = get_post_meta($postId, 'guests', true);

    if($guests == null || $guests == ""){
        $guests = array();
    }

    $guests[] = array(
        "first_name" => $_POST["first_name"],
        "last_name" => $_POST["last_name"],
        "email" => $_POST["email"],
    );

    update_post_meta($postId, 'guests', $guests);

    foreach(array(FLORENCE_HANCZAKOWSKI_EMAIL, PAULINE_EMAIL, SECRETARIAT_EMAIL) as $adminEmail){
        Mail::sendEmail($adminEmail, "Nouvelle demande d'accès à une publication",
            "61-publicationGuestToAdmin", array_merge($guests[count($guests) - 1],
                array("publicationTitle" => get_post($postId)->post_title)));
    }

    echo ("success");
    exit;
}

add_action('wp_ajax_find_user_by_email','find_user_by_email');
add_action('wp_ajax_nopriv_find_user_by_email','find_user_by_email');
function find_user_by_email(){
    $email = $_POST["email"];
    $response = array(
        "success" => false,
        "contact_first" => "",
        "contact_second" => "",
        "society" => "",
        "fonction" => "",
        "contact_phone" => "",
        "code" => "",
        "ville" => ""
    );
    $user = get_user_by("email", $email);

    if($user){
        $response["success"] = true;
        $response["contact_first"] = $user->first_name;
        $response["contact_second"] = $user->last_name;
        $response["society"] = get_post(get_user_meta($user->ID, "user_society", true))->post_title;
        $response["fonction"] = get_user_meta($user->ID, "user_poste", true);
        $response["contact_phone"] = get_user_meta($user->ID, "user_phone", true);
        $response["code"] = get_user_meta($user->ID, "codepostal", true);
        $response["ville"] = get_user_meta($user->ID, "ville", true);
    }

    echo json_encode($response);
    exit;
}
?>