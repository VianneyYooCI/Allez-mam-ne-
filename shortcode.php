<?php
add_shortcode('home_url', 'home_url_func');
function home_url_func()
{
    return home_url();
}

add_shortcode('list_conferences', 'list_conferences_func');
function list_conferences_func($attr)
{
    ob_start();
    $page = isset($attr['page']) ? $attr['page'] : 1;
    $cat = (isset($attr['cat'])) ? $attr['cat'] : "";
    ?>
    <ul class="partenaires">
        <?php
        if (isset($attr['to'])) {
            $from = ($attr['from'] == "") ? -9999999999 : strtotime($attr['from']);
            $to = ($attr['to'] == "") ? 99999999999 : strtotime($attr['to']);
            $query = array(
                'post_type' => 'conferences',
                'meta_key' => 'date_meta',
                'orderby' => 'meta_value',
                'posts_per_page' => -1, 'paged' => $page,
                'post_status' => array('publish', 'private'),
                'meta_query' => array(
                    array(
                        'key' => 'date_meta',
                        'value' => array($from, $to),
                        'compare' => 'BETWEEN',
                    )
                ),
                'tax_query' => array(
                    array(
                        'taxonomy' => 'conferences-cat',
                        'field' => 'slug',
                        'terms' => $cat,
                    ),
                )
            );
        } else {
            $query = array(
                'post_type' => 'conferences',
                'meta_key' => 'date_meta',
                'orderby' => 'meta_value',
                'order' => 'DESC',
                'posts_per_page' => 5, 'paged' => $page,
                'post_status' => array('publish', 'private'),
                'tax_query' => array(
                    array(
                        'taxonomy' => 'conferences-cat',
                        'field' => 'slug',
                        'terms' => $cat,
                    ),
                )
            );
        }
        $postAll = array();
        $the_query = new WP_Query($query);
        while ($the_query->have_posts()) {
            $the_query->the_post();
            $post = get_post();
            $date_meta = get_post_meta($post->ID, 'year_meta', true) . "/" . get_post_meta($post->ID, 'month_meta', true) . "/" . get_post_meta($post->ID, 'day_meta', true);
            $post->date_meta = strtotime($date_meta);
            $postAll[] = $post;
        }

        ?>
        <?php if(count($postAll) === 0){?>
            <b>Aucun résultat n'a été trouvé pour cette recherche</b>
        <?php }?>
        <?php
        foreach ($postAll as $post) {
            $term = wp_get_object_terms($post->ID, 'conferences-cat');
            $term_name = $term[0]->name;
            $content = "";
            $arrs = explode(" ", $post->post_content);
            $max_w = (count($arrs) > 24) ? 24 : count($arrs);
            for ($i = 0; $i < $max_w; $i++) {
                $content .= $arrs[$i] . " ";
            }
            $str = "";
            $arrs = array('address_meta', 'postal_meta', 'ville_meta', 'pays_meta');
            foreach ($arrs as $arr) {
                $str .= (get_post_meta($post->ID, $arr, true) <> "") ? " - " . get_post_meta($post->ID, $arr, true) : "";
            }

            ?>
            <li class="item">
                <img src="/content/uploads/2015/07/bag.png">

                <p class="date"><?php echo date('d/m/Y', $post->date_meta); ?>
                    <a class="<?php echo (strtotime(date('Y-m-d')) < strtotime(date('Y-m-d', $post->date_meta))) ? "venir" : ""; ?>">
                        <?php echo (strtotime(date('Y-m-d')) < strtotime(date('Y-m-d', $post->date_meta))) ? "à venir" : ''; ?>
                    </a>
                </p>

                <p><span class="address"><?php echo $str; ?></span></p>

                <p><a href="<?php echo get_permalink($post->ID) ?>"><h2
                            class="title title-left"><?php echo $post->post_title ?></h2></a></p>
                <div class="row">
                    <div class="col-md-8 col-xs-12"><p class="desc"><?php echo strip_tags($content); ?></p></div>
                    <div class="col-md-4 col-xs-12 button">
                        <a class="btn btn-detail" href="<?php echo get_permalink($post->ID) ?>">En savoir plus</a>
                    </div>
                </div>
            </li>
            <?php
        }
        ?>
    </ul>
    <div class="pag_control clearfix">
        <?php
        if ($page >= 2) {
            ?>
            <div class="pull-left"><a href="javascript:void(0)" class="pav_prev pag_click"
                                      data-page="<?php echo $page - 1; ?>" data-cat="<?php echo $cat; ?>"><i
                    class="fa fa-arrow-left"></i>Page précédente</a></div><?php }
        ?>
        <?php
        if ($page < $the_query->max_num_pages) {
            ?>
            <div class="pull-right"><a href="javascript:void(0)" class="pav_next pag_click"
                                       data-page="<?php echo $page + 1; ?>" data-cat="<?php echo $cat; ?>">Page suivante<i
                    class="fa fa-arrow-right"></i></a></div><?php }
        ?>
    </div>
    <?php
    wp_reset_query();
    return ob_get_clean();
}

add_shortcode('left_publications', 'left_publications_func');
function left_publications_func($attr)
{
    ob_start();
    ?>
    <script>
        window.onload = function () {
            $('.sub_category').hide();
            $('.category .iteme-ofcate label').on('click', function (e) {
                e.preventDefault();
                var dataId = $(this).find('input').data('id');
                if ($(this).find('input').hasClass('selected')) {
                    $('.sub_category.item-' + dataId).show();
                } else {
                    $('.sub_category.item-' + dataId).hide();
                }
            })
        };
    </script>
    <form class="filter_pub" action="return false" enctype="application/x-www-form-urlencoded" method="post">
        <div class="search">
            <h4>Recherche</h4>

            <div class="icon"><a style="cursor:pointer;" nohref><img src="/content/uploads/2015/08/icon-search.png"/></a>
            </div>
            <input type="text" placeholder="Recherche par mots clés" id="s_title"/>
        </div>
        <p>
            <h4>Filtrer par</h4>
            <select id="select_year" class="select_row">
                <option value="">Année</option>
                <?php
                $years = array();
                $arrs = get_posts(array('post_type' => 'publications', 'posts_per_page' => -1));
                foreach ($arrs as $arr) {
                    $year = date('Y', strtotime($arr->post_date));
                    if (!in_array($year, $years)) {
                        $years[] = $year;
                    }
                }
                foreach ($years as $year) {
                    ?>
                    <option value="<?php echo $year ?>"><?php echo $year; ?></option>
                    <?php
                }
                ?>
            </select>
        </p>
        <p>
            <select id="select_type" class="select_row">
                <option value="<?php echo $cats; ?>" selected="selected">Tous les formats</option>
                <?php
                $cats = "";
                $terms = get_terms('publications-format', array('hide_empty' => false,));
                foreach ($terms as $term) {
                    $cats .= $term->term_id . ",";
                    ?>
                    <option value="<?php echo $term->term_id ?>"><?php echo $term->name ?></option>
                    <?php
                }
                $cats = substr($cats, 0, -1);
                ?>
            </select>
        </p>
        <?php
        $arrs = array(
            'ar' => 'العربية',
            'az' => 'Azərbaycan dili',
            "ar" => "العربية",
            "az" => "Azərbaycan dili",
            "bg_BG" => "Български",
            "bs_BA" => "Bosanski",
            "ca" => "Català",
            "cy" => "Cymraeg",
            "da_DK" => "Dansk",
            "de_CH" => "Deutsch (Schweiz)",
            "de_DE" => "Deutsch",
            "el" => "Ελληνικά",
            "en_CA" => "English (Canada)",
            "en_GB" => "English",
            "en_AU" => "English (Australia)",
            "eo" => "Esperanto",
            "es_ES" => "Español",
            "es_MX" => "Español de México",
            "es_PE" => "Español de Perú",
            "es_CL" => "Español de Chile",
            "et" => "Eesti",
            "eu" => "Euskara",
            "fa_IR" => "فارسی",
            "fi" => "Suomi",
            "fr" => "France",
            "gd" => "Gàidhlig",
            "gl_ES" => "Galego",
            "haz" => "هزاره گی",
            "he_IL" => "עִבְרִית",
            "hr" => "Hrvatski",
            "hu_HU" => "Magyar",
            "id_ID" => "Bahasa Indonesia",
            "is_IS" => "Íslenska",
            "it_IT" => "Italiano",
            "ja" => "日本語",
            "ko_KR" => "한국어",
            "lt_LT" => "Lietuvių kalba",
            "nb_NO" => "Norsk bokmål",
            "nl_NL" => "Nederlands",
            "nn_NO" => "Norsk nynorsk",
            "oci" => "Occitan",
            "pl_PL" => "Polski",
            "ps" => "پښتو",
            "pt_PT" => "Português",
            "pt_BR" => "Português do Brasil",
            "ro_RO" => "Română",
            "ru_RU" => "Русский",
            "sk_SK" => "Slovenčina",
            "sl_SI" => "Slovenščina",
            "sq" => "Shqip",
            "sr_RS" => "Српски језик",
            "sv_SE" => "Svenska",
            "th" => "ไทย",
            "tl" => "Tagalog",
            "tr_TR" => "Türkçe",
            "ug_CN" => "Uyƣurqə",
            "uk" => "Українська",
            "zh_TW" => "繁體中文",
            "zh_CN" => "简体中文"

        )
        ?>
        <p>
            <select id="select_lang" class="select_row">
                <option value="">Tous les langages</option>
                <?php
                foreach ($arrs as $value => $name) {
                    $select = ($lang_meta == $value) ? 'selected="selected"' : '';
                    ?>
                    <option value="<?php echo $value ?>" <?php echo $select ?>><?php echo $name; ?></option>
                    <?php
                }
                ?>
            </select>
        </p>
        <p>
            <p style="display: none">
                <select id="select_pri" class="select_row">
                    <option value="">Privé/Public</option>
                    <?php
                    $terms = array('prive' => 'Privé', 'public' => 'Public', 'guest' => 'Restreint');
                    foreach ($terms as $value => $name) {
                        ?>
                        <option value="<?php echo $value ?>"><?php echo $name ?></option>
                        <?php
                    }
                    ?>
                </select>
            </p>
        </p>

        <div class="category category-publication" data-toggle="buttons">
            <h4 class="category-item">Par Thèmes</h4>
            <?php
            $arrs = get_terms('publications-cat', array('hide_empty' => false, 'parent' => 0));
            foreach ($arrs as $arr){
            ?>
                <div class="iteme-ofcate">
                    <label class="btn">
                        <input type="checkbox" data-id="<?php echo $arr->term_id ?>" class="check_pub check_m" value="0"/>
                        <span><?php echo $arr->name; ?></span>
                    </label>
                </div>
                <div class="check">
                    <?php
                    $akks = get_terms('publications-cat', array('hide_empty' => false, 'parent' => $arr->term_id));
                    foreach ($akks as $akk) {
                        ?>
                        <div class="sub_category item-<?php echo $arr->term_id ?>">
                            <div class="iteme-ofcate sub">
                                <label class="btn">
                                    <input type="checkbox" data-parent-id="<?php echo $arr->term_id; ?>"
                                           data-id="<?php echo $akk->term_id ?>" class="check_pub check_m" value="0"/>
                                    <span><?php echo $akk->name; ?></span>
                                </label>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <?php } ?>
        </div>
        <input type="hidden" id="cat_pub" value=""/>
        <input type="hidden" id="cat_parent_pub" value=""/>
    </form>
    <?php
    return ob_get_clean();
}

add_shortcode('list_publications', 'list_publications_func');
function list_publications_func($attr)
{
    ob_start();

    $query = array(
        'post_type' => 'publications',
        'orderby' => 'post_date', 'order' => 'DESC',
        'meta_query' => array(),
        'tax_query' => array('relation' => 'OR'),
        'posts_per_page' => -1,
        'status' => array('publish', 'private'),
    );

    $str = '';

    $taxonomiesId = array();
    $test = '';
    $saveParent = array();
    if (isset($attr['cat']) && $attr['cat'] !== "") {
        $categories = $attr['cat'];
        
        if($categories[strlen($categories) - 1] === ','){
            $categories = substr($categories, 0, strlen($categories) - 1);
        }
        if($_POST['cat_parent_pub'][strlen($_POST['cat_parent_pub']) - 1] === ','){
            $_POST['cat_parent_pub'] = substr($_POST['cat_parent_pub'], 0, strlen($_POST['cat_parent_pub']) - 1);
        }
        if(isset($_POST['cat_parent_pub']) && $_POST['cat_parent_pub'] != ''){
            $tabParent = explode(',', $_POST['cat_parent_pub']);
        }



        foreach (explode(',', $categories) as $id) {
            
            if(isset($tabParent) && in_array($id, $tabParent)){
                $item = Shortcode::getTaxonomyById($id);                
                $str .= $item[0]->name.'/'; 

            }else{
                $item = Shortcode::getTaxonomyById($id);
                $taxonomiesId[] = $item[0]->term_id;
                $str .= $item[0]->name.'/'; 
            }            
            
        }



        if(count($taxonomiesId)){

            $query["tax_query"][] = array(
                'taxonomy' => 'publications-cat',
                'terms' => $taxonomiesId,
                'operator' => 'IN'
            );
           
        }
    }

    if(isset($_POST['select_year']) && $_POST['select_year'] != ''){
        $year = $_POST['select_year'];
        $query["date_query"] = array(
            array(
                'year'  => $year
            ),
        );
        $str.= $year.'/';
    }

    if(isset($_POST["select_type"]) && $_POST["select_type"] != ''){
        $type = get_term($_POST["select_type"], 'publications-format')->name;
        $query["tax_query"][] = array(
                'taxonomy' => 'publications-format',
                'terms' => $_POST["select_type"],
            );
        $str.= $type.'/';
    }

    if(isset($_POST["select_lang"]) && $_POST["select_lang"] != ''){
        $lang = $_POST["select_lang"];
        $query["meta_query"][] = array(
            'key'     => 'lang_meta',
            'value'   => $lang,
            'compare' => '=',
        );
        $str.= $lang.'/';
    }

    if(isset($_POST ["s_title"]) && $_POST ["s_title"] != ''){
        $title = $_POST ["s_title"];
        $query["wpse18703_title"] = $title;
        $str.= $title.'/';
    }

    if($str[strlen($str) - 1] === '/'){
        $str = substr($str, 0, strlen($str) - 1);
    }


    $postAllPub = array();
    $the_query = new WP_Query($query);

    while ($the_query->have_posts()) {
        $the_query->the_post();
        $post = get_post();
        $postAllPub[] = $post;
    }

    ?>
    <div class="number"><?php echo $str . " - " . count($postAllPub) . " documents";
        if(count($postAllPub) === 0){
            echo "<br /> Aucun résultat n'a été trouvé pour cette recherche";
        }?>
    </div>
    <ul class="document-items">
        <?php foreach ($postAllPub as $post) {
            $formats = wp_get_object_terms($post->ID, 'publications-format');
            $format = '';

            if (isset($formats[0]) && $formats[0] != '') {
                $format = ($formats[0]->slug == "video") ? "video" : "pdf";
            }

            $term = wp_get_object_terms($post->ID, 'publications-cat');
            $str = "";
            $str .= count($term) ? "<li>" . $term[0]->name . "</li>" : "";
            $str .= "<li>Publié : " . date('d-M-Y', strtotime($post->post_date)) . "</li>";
            $str .= "<li>Lang: " . get_post_meta($post->ID, 'lang_meta', true) . "</li>";
            $str .= (get_post_meta($post->ID, 'visible_meta', true) == "prive") ? "<li><span>privé</span></li>" : "";
            ?>
            <li class="row item">
                <div class="col-md-2 col-xs-3 item-image">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/<?php echo $format ?>.png"
                         width="50"
                         height="50"/>
                </div>
                <div class="col-md-7 col-xs-9 item-detail">
                    <p><?php echo $post->post_title ?></p>
                    <ul class="category">
                        <?php echo $str; ?>
                    </ul>
                    <?php if(get_post_meta($post->ID, 'visible_meta', true) === "prive"){?>
                        <p style="color: red;">Accès réservé aux membres du CLUSIF</p>
                    <?php }?>
                </div>
                <div class="col-md-3 col-xs-12 button "><a class="btn btn-detail" href="<?php echo home_url()?>/<?php echo $post->post_type ?>/<?php echo $post->post_name; ?>">Consulter</a></div>
            </li>
        <? }?>
    </ul>
    <?php
    return ob_get_clean();
}

add_shortcode('left_media', 'left_media_func');
function left_media_func($attr)
{
    ob_start();
    ?>
    <script>
        window.onload = function () {
            $('.sub_category').hide();
            $('.category-media .iteme-ofcate label').on('click', function (e) {
                e.preventDefault();
                var dataId = $(this).find('input').data('id');
                if ($(this).find('input').hasClass('selected')) {
                    $('.sub_category.item-' + dataId).show();
                } else {
                    $('.sub_category.item-' + dataId).hide();
                }
            })
        };
    </script>
    <form class="media_submit" action="return false" enctype="application/x-www-form-urlencoded" method="post">
    <div class="filter_par date module-left">
        <div class="top-sidebar">
            <h2 class="title title-center">
                <img src="/content/uploads/2015/08/bag-big.png"></h2>
            </h2>
            <h4>TRIÉES PAR ANNÉE :</h4>

            <p> Sélectionnez une période à afficher.</p>
        </div>
        <p class="input">From: <input id="from_date" name="from_date" type="text"/></p>

        <p class="input">To: <input id="to_date" name="to_date" type="text"/></p>

        <div id="date-range-container"></div>
    </div>
    <div class="module_search module-left">
        <h4>Recherche</h4>
        <input type="text" class="search_ok" placeholder="Recherche par mots clés" name="key" id="key" value=""/>
    </div>
    <div class="category category-media module-left" data-toggle="buttons">
        <h4>
                <span class="visible-sm-inline visible-md-inline visible-lg-inline">
                    Catégories
                </span>
                <span class="hidden-sm hidden-md hidden-lg">
                    <span data-target="#categori-items" data-toggle="collapse" aria-expanded="false"
                          aria-controls="categori-items">
                        Catégories
                        <span class="caret"></span>
                    </span>
                </span>
        </h4>
        <div id="categori-items">
            <?php
            $arrs = get_terms('medias-cat', array('hide_empty' => false, 'parent' => 0));
            foreach ($arrs as $arr) {
                ?>
                <div class="iteme-ofcate double">
                    <label class="btn">
                        <input id="cat_media" type="checkbox" data-id="<?php echo $arr->term_id ?>"
                               class="check_med check_m" value="0"/>
                        <span><?php echo $arr->name; ?></span>
                    </label>
                </div>
                <div class="check">
                    <?php
                    $akks = get_terms('medias-cat', array('hide_empty' => false, 'parent' => $arr->term_id));
                    foreach ($akks as $akk) {
                        ?>
                        <div class="sub_category item-<?php echo $arr->term_id ?>">
                            <div class="iteme-ofcate sub">
                                <label class="btn">
                                    <input type="checkbox" data-parent-id="<?php echo $arr->term_id; ?>"
                                           data-id="<?php echo $akk->term_id ?>" class="check_med check_m" value="0"/>
                                    <span><?php echo $akk->name; ?></span>
                                </label>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
    <input type="hidden" id="media" name="media" value="">
    </form>
    <?php
    return ob_get_clean();
}


add_shortcode('list_media', 'list_media_func');
function list_media_func($attr)
{
    ob_start();
    if (isset($_POST['page'])) {
        $attr['page'] = $_POST['page'];
    }
    if (isset($_POST['media'])) {
        $attr['cat'] = $_POST['media'];
    }
    $paged = (isset($attr['page'])) ? $attr['page'] : 1;
    $num = (isset($attr['key'])) ? -1 : 20;

    ?>
    <ul>

        <div class="presse clearfix">
            <!--<h5 class="pull-left" ><?php echo (isset($attr['cat']))?$attr['cat']:"" ?></h5>-->
            <div class="numbers pull-left"><?php
        if (isset($attr['cat'])) {
            $from = ($attr['from'] == "") ? -9999999999 : strtotime($attr['from']);
            $to = ($attr['to'] == "") ? 99999999999 : strtotime($attr['to']);
            $cat = explode(',', $attr['cat']);
            if (empty($attr['cat'])) {
                $query = array(
                    'post_type' => 'medias',
                    'meta_key' => 'date_meta',
                    'orderby' => 'meta_value_num',
                    'order' => 'DESC',
                    'paged' => $paged,
                    'posts_per_page' => $num,
                    'post_status' => array('publish', 'private'),
                    'meta_query' => array(
                        array(
                            'key' => 'date_meta',
                            'value' => array($from, $to),
                            'compare' => 'BETWEEN',
                        )
                    ),
                );
            } else {
                $query = array(
                    'post_type' => 'medias',
                    'meta_key' => 'date_meta',
                    'orderby' => 'meta_value_num',
                    'order' => 'DESC',
                    'posts_per_page' => $num,
                    'paged' => $paged,
                    'post_status' => array('publish', 'private'),
                    'meta_query' => array(
                        array(
                            'key' => 'date_meta',
                            'value' => array($from, $to),
                            'compare' => 'BETWEEN',
                        )
                    ),
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'medias-cat',
                            'field' => 'id',
                            'terms' => $cat,
                        ),
                    )
                );
            }
        } else {
            $query = array(
                'post_type' => 'medias',
                'orderby' => 'date', 'order' => 'desc',
                'posts_per_page' => $num,
                'paged' => $paged,
                'status' => array('publish', 'private'),
            );
        }

        $postMedia = array();
        $the_query = new WP_Query($query);
        while ($the_query->have_posts()) {
            $the_query->the_post();
            $post = get_post();
            $postMedia[] = $post;
        }
        if (isset($_POST['media'])) {
            $taxonomy = explode(',', $_POST['media']);
            foreach ($taxonomy as $id)
                $result[] = Shortcode::getTaxonomyById($id);
        }

        if (isset($_POST['cat_media'])) {
            $taxonomy = explode(',', $_POST['cat_media']);
            foreach ($taxonomy as $id)
                $result[] = Shortcode::getTaxonomyById($id);
        }
        if (isset($result) && count($result)) {
            foreach ($result as $item) {
                if (count($item)) {
                    $parentName = Shortcode::getNameTaxonomy(Shortcode::getParentTaxonomy($item[0]->term_id)[0]->parent);
                    if (isset($parentName) && count($parentName)) {
                        echo $parentName[0]->name; ?>/<?php
                        echo $item[0]->name; ?>/<?php
                    } else {
                        echo $item[0]->name; ?><?php
                    }
                }
            };
        }
        $count = count($postMedia);
        if($count === 0){
            echo "Il n'y a aucune communication correspondant à cette recherche.";
        }
        ?></div>
            <ul class="pav">
                <?php
                if ($paged > 1) {
                    ?>
                    <li><a href="javascript:void(0)" data-page="<?php echo($paged - 1) ?>"
                           class="prev_pav pav_list_media"><span><i class="fa fa-arrow-left"></i></span></a></li>
                    <?php
                }
                ?>
                <?php
                for ($i = 1; $i < $the_query->max_num_pages; $i++) {
                    $act = ($i == $paged) ? 'active' : '';
                    if ($i == $the_query->max_num_pages or $i <= 3 or $i == $paged) {
                        ?>
                        <li><a href="javascript:void(0)" data-page="<?php echo $i ?>"
                               class="pav_list_media <?php echo $act; ?>"><?php echo $i; ?></a></li>
                        <input type="hidden" id="page" name="page"/>
                        <?php
                        if ($i == 3 and $paged < 3) {
                            echo "...";
                        }
                    } else {
                        if ($i == ($paged - 1)) {
                            if ($paged >= 6) {
                                echo "..";
                            }
                            ?>
                            <li><a href="javascript:void(0)" data-page="<?php echo $i ?>"
                                   class="pav_list_media <?php echo $act; ?>"><?php echo $i; ?></a></li>
                            <?php
                        }
                        if ($i == ($paged + 1)) {
                            ?>
                            <li><a href="javascript:void(0)" data-page="<?php echo $i ?>"
                                   class="pav_list_media <?php echo $act; ?>"><?php echo $i; ?></a></li>
                            <?php
                            if ($i < $the_query->max_num_pages) {
                                echo "..";
                            }
                        }

                    }
                }
                ?>

                <li><a href="javascript:void(0)" data-page="<?php echo $i ?>"
                       class="pav_list_media <?php echo ($paged == $the_query->max_num_pages) ? 'active' : ''; ?>"><?php echo $the_query->max_num_pages; ?></a>
                </li>
                <?php
                if ($paged < $the_query->max_num_pages) {
                    ?>
                    <li><a href="javascript:void(0)" data-page="<?php echo($paged + 1) ?>"
                           class="prev_pav pav_list_media"><span><i class="fa fa-arrow-right"></i></span></a></li>
                    <?php
                }
                ?>

            </ul>
        </div>

        <?php

        while ($the_query->have_posts()) {
            $the_query->the_post();
            $post = get_post();
            $str = "";
            $str .= (get_post_meta($post->ID, 'source_meta', true) <> "") ? "<li>" . get_post_meta($post->ID, 'source_meta', true) . "</li>" : "";
            $str .= "<li>" . date('d-M-Y', get_post_meta($post->ID, 'date_meta', true)) . "</li>";
                ?>
                <li class="item">
                    <ul class="category">
                        <p style="font-weight:500; "><?php echo $post->post_title ?></p>
                        <?php echo $str; ?>
                    </ul>
                    <?php
                    if (get_post_meta($post->ID, 'location_meta', true) <> "") {
                        ?>
                        <div class="button"><a target="_blank" href="<?php echo get_post_meta($post->ID, 'location_meta', true); ?>"
                                               class="btn btn-detail">Consulter</a></div>
                        <?php
                    }
                    ?>
                </li>
                <?php

        }
        ?>
    </ul>


    <ul class="pav">
        <?php
        if ($paged > 1) {
            ?>
            <li><a href="javascript:void(0)" data-page="<?php echo($paged - 1) ?>"
                   class="prev_pav pav_list_media"><span><i class="fa fa-arrow-left"></i></span></a></li>
            <?php
        }
        ?>
        <?php


        for ($i = 1; $i < $the_query->max_num_pages; $i++) {
            $act = ($i == $paged) ? 'active' : '';
            if ($i == $the_query->max_num_pages or $i <= 3 or $i == $paged) {
                ?>
                <li><a href="javascript:void(0)" data-page="<?php echo $i ?>"
                       class="pav_list_media <?php echo $act; ?>"><?php echo $i; ?></a></li>
                <?php
                if ($i == 3 and $paged < 3) {
                    echo "...";
                }

            } else {
                if ($i == ($paged - 1)) {
                    if ($paged >= 6) {
                        echo "..";
                    }
                    ?>
                    <li><a href="javascript:void(0)" data-page="<?php echo $i ?>"
                           class="pav_list_media <?php echo $act; ?>"><?php echo $i; ?></a></li>
                    <?php
                }

                if ($i == ($paged + 1)) {
                    ?>
                    <li><a href="javascript:void(0)" data-page="<?php echo $i ?>"
                           class="pav_list_media <?php echo $act; ?>"><?php echo $i; ?></a></li>
                    <?php
                    if ($i < $the_query->max_num_pages) {
                        echo "..";
                    }
                }

            }
        }
        ?>

        <li><a href="javascript:void(0)" data-page="<?php echo $i ?>"
               class="pav_list_media <?php echo ($paged == $the_query->max_num_pages) ? 'active' : ''; ?>"><?php echo $the_query->max_num_pages; ?></a>
        </li>
        <?php
        if ($paged < $the_query->max_num_pages) {
            ?>
            <li><a href="javascript:void(0)" data-page="<?php echo($paged + 1) ?>"
                   class="prev_pav pav_list_media"><span><i class="fa fa-arrow-right"></i></span></a></li>
            <?php
        }
        ?>

    </ul>

    <?php


    wp_reset_query();

    return ob_get_clean();

}

add_shortcode('list_clusi', 'list_clusi_func');
function list_clusi_func($attr)
{
    $col = (isset($attr['col'])) ? (12 / $attr['col']) : 4;
	$buttontext=(isset($attr['button']))?$attr['button']:'En savoir plus';
    ob_start();
    ?>
    <ul class="row">
        <?php
        $arrs = get_posts(array('post_type' => 'clusi-clusir', 'posts_per_page' => -1, 'meta_query' => array(array('key' => 'select_clu', 'value' => $attr['cat'], 'compare' => '=')), 'orderby' => 'post_title', 'order' => 'ASC'));
        foreach ($arrs as $arr) {
            $url_img = (get_post_thumbnail_id($arr->ID) <> "") ? wp_get_attachment_image_src(get_post_thumbnail_id($arr->ID), array(200, 200)) : array('');
            $link = (get_post_meta($arr->ID, 'site_meta', true) <> "") ? get_post_meta($arr->ID, 'site_meta', true) : get_permalink($arr->ID);
            ?>
            <li class="col-md-<?php echo $col; ?> col-xs-6 group">
                <div class="item">
                    <div style="text-align: center;"><img src="<?php echo $url_img[0]; ?>"/></div>
                    <h3><?php echo $arr->post_title; ?></h3>

                    <p class="desc"><?php echo get_post_meta($arr->ID, 'desc_meta', true); ?></p>

                    <div class="group-clusir-item"><a class="f-text btn btn-normal" href="<?php echo $link; ?>"><?php echo $buttontext; ?></a></div>
                </div>
            </li>
            <?php
        }
        ?>
    </ul>

    <?php
    return ob_get_clean();
}

add_shortcode('list_temo', 'list_temo_func');
function list_temo_func()
{
    ob_start();
    ?>
    <ul class="row temo">
        <?php
        $arrs = get_posts(array('post_type' => 'temoignage', 'posts_per_page' => -1));
        foreach ($arrs as $arr) {
            $url_img = (get_post_thumbnail_id($arr->ID) <> "") ? wp_get_attachment_image_src(get_post_thumbnail_id($arr->ID), array(200, 200)) : array('');
            ?>
            <li>
                <div class="col-md-1 col-xs-4" ><img src="<?php echo $url_img[0]; ?>"/></div>
                <div class="col-md-3 col-xs-8 st">
                    <ul>
                        <li><?php echo get_post_meta($arr->ID, 'nom_meta', true); ?> <?php echo get_post_meta($arr->ID, 'prenom_meta', true); ?></li>
                        <li><?php echo get_post_meta($arr->ID, 'societe_meta', true); ?></li>
                        <li><?php echo get_post_meta($arr->ID, 'fonction_meta', true); ?></li>
                    </ul>
                </div>
                <div class="col-md-8 col-xs-12"><?php
                    if (get_post_meta($arr->ID, 'select_layout', true) == "video") {
                        ?>
                        <div class="rowh">
                            <div class="col-md-5">
                                <video width="100%" controls>
                                    <source src="<?php echo get_post_meta($arr->ID, 'url_video', true) ?>"
                                            type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                            <div class="col-md-7"><?php echo $arr->post_content; ?></div>
                        </div>
                        <?php
                    } else {
                        echo $arr->post_content;
                    } ?></div>
            </li>
            <?php
        }
        ?>
    </ul>
    <?php
    return ob_get_clean();
}

add_shortcode('list_spon', 'list_spon_func');
function list_spon_func()
{
    ob_start();
    ?>
    <ul class="rowder">
        <?php
        $arrs = get_posts(array('post_type' => 'sponsors', 'posts_per_page' => -1));
        foreach ($arrs as $arr) {
            $url = "";
            if (!get_post_meta($arr->ID, 'logo_meta', true) == "") {
                $url = wp_get_attachment_url(get_post_meta($arr->ID, 'logo_meta', true));
            }
            ?>
            <li>
                <div class="logo"><a href="<?php echo get_post_meta($arr->ID,'location_meta',true); ?>" target="_blank"><img src="<?php echo $url; ?>"/></a></div>
                <div class="content">
                    <!--<h3><?php echo $arr->post_title; ?></h3>-->

                    <p><?php echo get_post_meta($arr->ID, 'desc_meta', true) ?></p>
                    <!-- <a href="<?php //echo get_post_meta($arr->ID,'location_meta',true);
                    ?>">En savoir plus</a> -->
                </div>
            </li>
            <?php
        }
        ?>
    </ul>
    <?php
    return ob_get_clean();
}

add_shortcode('pdf_file', 'pdf_file_func');
function pdf_file_func($attr)
{
    ob_start();
    ?>
    <iframe src="<?php echo $attr['src'] ?>" width="<?php echo $attr['w'] ?>"
            height="<?php echo $attr['h'] ?>"></iframe>
    <?php
    return ob_get_clean();
}

add_shortcode('lasted_conferences', 'lasted_conferences_func');
function lasted_conferences_func()
{
    ob_start();
    $arrs = get_posts(array('post_type' => 'conferences', 'posts_per_page' => 4, 'orderby' => 'date', 'order' => 'DESC'));
    foreach ($arrs as $arr) {
        $content = "";
        $kks = explode(" ", $arr->post_content);
        $max_w = (count($kks) > 20) ? 20 : count($kks);
        for ($i = 0; $i < $max_w; $i++) {
            $content .= $kks[$i] . " ";
        }
        ?>
        <div class="col-md-3 col-xs-6">
            <div class="item">
                <h4 class="title title-left"><a class="date"
                                                href="<?php echo get_permalink($arr->ID) ?>"><?php echo date('d/m/y', get_post_meta($arr->ID, 'date_meta', true)) ?></a><a
                        class="post-name"
                        href="<?php echo get_permalink($arr->ID) ?>"><?php echo $arr->post_title ?></a></h4>

                <p class="short"><?php echo strip_tags($content); ?></p>
            </div>
        </div>
        <?php
    }
    return ob_get_clean();
}

add_shortcode('last_sponsor', 'last_sponsor_func');
function last_sponsor_func()
{
    ob_start();
    $arrs = get_posts(array('post_type' => 'sponsors', 'posts_per_page' => 6, 'orderby' => 'date', 'order' => 'DESC'));
    foreach ($arrs as $arr) {
        $img_id = get_post_meta($arr->ID, 'logo_meta', true);
        $url_img = ($img_id <> "") ? wp_get_attachment_image_src($img_id, array(150, 80)) : array('');
        ?>
        <div class="col-md-2 col-xs-6">
            <div class="item">
                <a href="/sponsors/"><img src="<?php echo $url_img[0] ?>"></a>
            </div>
        </div>
        <?php
    }
    return ob_get_clean();
}

/*--------------------------------list list_glossaire-------------------*/
add_shortcode('list_glossaire', 'list_glossaire_func');
function list_glossaire_func($attr)
{
    ob_start();
    $cat = $attr['lettre'];
    $arrs = get_posts(array('post_type' => 'glossaire', 'posts_per_page' => -1, 'tax_query' => array(array('taxonomy' => 'letter-cat', 'field' => 'slug', 'terms' => $cat,),)));
    foreach ($arrs as $arr) {
        ?>
        <div class="item-glossaire">
            <h3><a href="<?php echo get_permalink($arr->ID) ?>"><?php echo $arr->post_title; ?></a></h3>

            <p><?php echo apply_filters('the_content', $arr->post_content); ?></p>
        </div>
        <?php
    }
    return ob_get_clean();
}

/*--------------------------------list letter-------------------*/
add_shortcode('list_lettre', 'list_lettre_func');
function list_lettre_func($attr)
{
    ob_start();
    $arrs = get_terms('letter-cat', array('hide_empty' => false));
    echo (count($arrs) >= 1) ? '<ul class="list-lettre">' : '';
    foreach ($arrs as $arr) {
        ?>
        <li><a nohref style="cursor: pointer" data-lettre="<?php echo $arr->slug ?>"
               class="load-lettre"><?php echo $arr->name ?></a></li>
        <?php
    }
    echo (count($arrs) >= 1) ? '</ul>' : '';
    ?>
    <div class="mobile-letter">
        <select id="select-lettre">
            <option value="">Select a letter/Selectionnez une lettre</option>
            <?php
            $arrs = get_terms('letter-cat', array('hide_empty' => false));
            foreach ($arrs as $arr) {
                ?>
                <option value="<?php echo $arr->slug ?>"><?php echo $arr->name ?></option>
                <?php
            }
            ?>
        </select>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('set_background_image_membership', 'set_background_image_membership_func');
function set_background_image_membership_func($attr)
{
    ob_start();
    $pageName = $attr["page_url"];
    $imageUrl = get_post(get_post_meta(get_page_by_path($pageName)->ID,'image_for_membership_id',true))->guid;
    ?>
    <div id="membership" class="row" style="background: rgba(0, 0, 0, 0) url('<?php echo $imageUrl;?>') no-repeat scroll center center / cover ;">
    <?php return ob_get_clean();
}

add_shortcode('set_pdf_file_on_button', 'set_pdf_file_on_button_func');
function set_pdf_file_on_button_func($attr)
{
    ob_start();
    $pageName = $attr["page_url"];
    $pdfUrl = get_post(get_post_meta(get_page_by_path($pageName)->ID,'pdf_file_for_button',true))->guid;
    ?>
        <a class="btn btn-light" target="_blank" href="<?php echo $pdfUrl;?>">en cliquant ici</a>
    <?php return ob_get_clean();
}

/*----------add act page----*/

add_shortcode('list_act_page', 'list_act_page_f');
function list_act_page_f()
{
    global $post;
    ob_start();
    $datas = json_decode(get_post_meta($post->ID, 'data_act', true));
    foreach ($datas as $data) {
        //$url_img = ($data->image <> "") ? wp_get_attachment_image_src($data->image, array(100, 102)) : array('');
		 //$url_img= wp_get_attachment_url( get_post_thumbnail_id($data->image) );
		 $url_img = ($data->image <> "") ? wp_get_attachment_image_src($data->image,'full') : array('');
        ?>
        <div class="tear clusif-item">
            <div class=" col-md-3">
                <div class="images">
                    <a href="javascript:void(0)"><img src="<?php echo $url_img[0] ?>" alt="" /></a>
                </div>
            </div>
            <div class="col-md-9">
                <h4><?php echo $data->day . "/" . $data->month . "/" . $data->year; ?></h4>

                <div class="lorem"><?php echo $data->title ?></div>
                <p><?php echo Utils::removeSpecCaractersFromString($data->desc); ?></p>
            </div>
        </div>
        <?php
    }
    return ob_get_clean();
}

add_shortcode('access_act_page', 'access_act_page_f');
function access_act_page_f()
{
    if(!is_user_logged_in()){
        wp_redirect(home_url()."/login", 301);
        exit;
    }

    return ob_get_clean();
}

/*-------------shortcode content widget------*/
add_shortcode('content_sidebar', 'content_sidebar_func');
function content_sidebar_func($attr)
{
    ob_start();
    if (is_active_sidebar($attr['id'])) {
        dynamic_sidebar($attr['id']);
    }
    return ob_get_clean();
}

add_shortcode('document_list', 'document_list_func');
function document_list_func($attr)
{
    if(!is_user_logged_in()){
        wp_redirect(home_url().'/login', 301);
        exit;
    }

    if(User::isGuestRole()){
        wp_redirect(home_url());
        exit;
    }

    ob_start();?>
    <h4 class="title-module"><i class="fa fa-file-text"></i>Documents utiles</h4><?php
    $list = DocumentUtiles::findAll();
    foreach ($list as $item) { ?>
        <div class="document-column">
            <?php $fileData = get_post_meta($item->ID, 'wp_custom_attachment', true);
            if (is_array($fileData) && array_key_exists('url', $fileData)) { ?>
                <div class="title-document">
                    <a target="_blank" href="<?php echo $fileData['url'] ?>"><?php echo $item->post_title; ?></a>
                </div>
            <?php } else {
                echo '';
            } ?>
            <p><?php echo $item->post_content; ?></p>
        </div>
    <?php }
    return ob_get_clean();
}

    add_shortcode('list_workgroup_events', 'list_workgroup_events_func');
    function list_workgroup_events_func($attr)
    {
        global $post;
        ob_start();
        $workgroupId = $attr["workgroup"];
        $monthNames = array("Janvier", "Fevrier", "Mars", "Avril", "Mai", "Juin", "Juillet", "Aout", "Septembre", "Octobre",
            "Novembre", "Decembre");

        $events = Workgroup::findByCategory($workgroupId);
        function cmpEventsDates($a, $b)
        {
            $timeA = date("U", strtotime(get_post_meta($a->ID, 'date', true) . get_post_meta($a->ID, 'timeFrom', true)));
            $timeB = date("U", strtotime(get_post_meta($b->ID, 'date', true) . get_post_meta($b->ID, 'timeFrom', true)));
            return $timeA >= $timeB;
        }
        usort($events, "cmpEventsDates");
        //remove earlier that today
        foreach($events as $key => $item){
            if(date("U", strtotime(get_post_meta($item->ID, 'date', true) . get_post_meta($item->ID, 'timeFrom', true))) <
                date("U")){
                unset($events[$key]);
            }
            else{
                break;
            }
        }
        $events = array_values($events);
        /*foreach($events as $event) {
            $timeFrom = get_post_meta($event->ID, 'timeFrom', true);
            $timeTo = get_post_meta($event->ID, 'timeTo', true);
            $date = get_post_meta($event->ID, 'date', true);
            $date = explode('-', $date); ?>
            <li><?php echo intval($date[2]) . ' ' . strtolower($monthNames[intval($date[1]) - 1]) . " de " . $timeFrom . " à " . $timeTo;?></li>*/
            if(count($events)<3){
                $nbEventDisplay = count($events);
            }else{
                $nbEventDisplay = 3;
            }
            for($index = 0; $index <$nbEventDisplay; $index++){
              $timeFrom = get_post_meta($events[$index]->ID, 'timeFrom', true);
              $timeTo = get_post_meta($events[$index]->ID, 'timeTo', true);
              $date = get_post_meta($events[$index]->ID, 'date', true);
              $date = explode('-', $date); ?>
              <li><?php echo intval($date[2]) . ' ' . strtolower($monthNames[intval($date[1]) - 1]) . " de " . $timeFrom . " à " . $timeTo;?></li>  
        <?php }
        return ob_get_clean();
    }

?>
