<?php get_header(); ?>
<?php
$id_tournoi      = get_the_ID();
$list_contenders = array();
$all_user_votes       = new WP_Query(array(
    'post_type'      => 'vote',
    'posts_per_page' => -1,
    'meta_query'     => array(
        'relation'   => 'AND',
        array(
            'key'     => 'id_t_v',
            'value'   => $id_tournoi,
            'compare' => '=',
        ),
        array(
            'key'     => 'id_user_v',
            'value'   => $_COOKIE["vainkeurz_user_id"],
            'compare' => '=',
        )
    )
));
$nb_user_votes = $all_user_votes->post_count;

$contenders      = new WP_Query(array(
    'post_type'      => 'contender',
    'posts_per_page' => -1,
    'orderby'        => 'date',
    'meta_query'     => array(
        array(
            'key'     => 'id_tournoi_c',
            'value'   => $id_tournoi,
            'compare' => '=',
        )
    )
));
$i=0; while ($contenders->have_posts()) : $contenders->the_post();

    array_push($list_contenders, get_the_ID());

    $rand_c = array_rand($list_contenders, 2);
    $id_c_1 = $list_contenders[$rand_c[0]];
    $id_c_2 = $list_contenders[$rand_c[1]];

$i++; endwhile;
wp_reset_query();
?>
<?php if(isset($_GET['classement']) && $_GET['classement'] == "show") : ?>

    <div class="classement">
        <div class="container-fluid">
            <div class="row">
                <div class="col">
                    <div class="titre-classement text-center">
                        <h2>Classement en cours</h2>
                    </div>
                </div>
            </div>
            <div class="list-classement">
                <div class="row">
                    <?php
                    $contenders_top = new WP_Query(
                        array(
                            'post_type'      => 'contender',
                            'posts_per_page' => 3,
                            'meta_key'       => 'ELO_c',
                            'orderby'        => 'meta_value_num',
                            'order'          => 'DESC',
                            'meta_query'     => array(
                                array(
                                    'key'     => 'id_tournoi_c',
                                    'value'   => $id_tournoi,
                                    'compare' => '=',
                                )
                            )
                        )
                    );
                    $i=1; while ( $contenders_top->have_posts() ) : $contenders_top->the_post(); ?>

                        <?php
                        if($i == 1){
                            $class      = "col-12";
                            $intitule   = "1er";
                        }
                        elseif($i == 2){
                            $class      = "col-sm-4 offset-sm-1 col-6";
                            $intitule   = "2ème";
                        }
                        elseif($i == 3){
                            $class      = "col-sm-4 offset-sm-1 col-6";
                            $intitule   = "3ème";
                        }
                        elseif($contenders->post_count == $i){
                            $class      = "col-12";
                            $intitule   = "Dernier";
                        }
                        else{
                            $class      = "col-md-3 col-sm-4 col-4 col-lg-2";
                            $intitule   = $i;
                        }
                        ?>

                        <div class="contenders_min <?php echo $class; ?>">

                            <div class="illu">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <?php the_post_thumbnail( 'full', array( 'class' => 'img-fluid' ) ); ?>
                                <?php endif; ?>
                            </div>
                            <div class="name">
                                <h5 class="toshowpopover" data-container="body" data-toggle="popover" data-placement="top" data-content="ELO : <?php the_field('ELO_c'); ?>">
                                    <span><?php echo $intitule; ?></span>
                                    <br>
                                    <?php the_title(); ?>
                                </h5>
                                <h6 class="none">
                                    <?php the_field( 'ELO_c' ); ?>
                                </h6>
                            </div>

                        </div>

                    <?php $i++; endwhile; wp_reset_query(); ?>
                </div>
                <div class="reste-classement">
                    <div class="row">
                        <?php
                        $contenders_reste = new WP_Query(
                            array(
                                'post_type'      => 'contender',
                                'posts_per_page' => 1000,
                                'meta_key'       => 'ELO_c',
                                'orderby'        => 'meta_value_num',
                                'offset'         => 3,
                                'order'          => 'DESC',
                                'meta_query'     => array(
                                    array(
                                        'key'     => 'id_tournoi_c',
                                        'value'   => $id_tournoi,
                                        'compare' => '=',
                                    )
                                )
                            )
                        );
                        $i=4; while ( $contenders_reste->have_posts() ) : $contenders_reste->the_post(); ?>

                            <?php
                            if($contenders->post_count == $i){
                                $class      = "col-12";
                                $intitule   = "Dernier";
                            }
                            else{
                                $class      = "col-md-3 col-sm-4 col-4 col-lg-2";
                                $intitule   = $i;
                            }
                            ?>

                            <div class="contenders_min <?php echo $class; ?>">

                                <div class="illu">
                                    <?php if ( has_post_thumbnail() ) : ?>
                                        <?php the_post_thumbnail( 'full', array( 'class' => 'img-fluid' ) ); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="name">
                                    <h5 class="toshowpopover" data-container="body" data-toggle="popover" data-placement="top" data-content="ELO : <?php the_field('ELO_c'); ?>">
                                        <span><?php echo $intitule; ?></span>
                                        <br>
                                        <?php the_title(); ?>
                                    </h5>
                                    <h6 class="none">
                                        <?php the_field( 'ELO_c' ); ?>
                                    </h6>
                                </div>

                            </div>

                        <?php $i++; endwhile; wp_reset_query(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>

    <div class="main">
        <header class="header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-sm-4">
                        <div class="logo">
                            <a href="<?php bloginfo('url'); ?>/">
                                <img src="<?php bloginfo('template_directory'); ?>/assets/img/logo-vainkeurz.png" alt="" class="img-fluid">
                            </a>
                        </div>
                    </div>
                    <div class="col-sm-8 text-right">
                        <div class="display_users_votes">
                            <a href="https://baltazare1.typeform.com/to/j9n8JU" target="_blank" class="cta_2">
                                ☝️ Donnez nous votre avis !
                            </a>
                            <h6>
                                <?php if ($nb_user_votes == 0) : ?>
                                    Aucun vote encore
                                <?php elseif ($nb_user_votes == 1) : ?>
                                    Bravo pour ton 1er vote
                                <?php else : ?>
                                    Vos votes : <?php echo $all_user_votes->post_count; ?>
                                <?php endif; ?>
                            </h6>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <div class="container">
            <div class="tournoi_infos">
                <div class="row align-items-center">
                    <div class="col-12">
                        <div class="bloc-titre">
                            <h1 class="title-battle">
                                <b>
                                    <?php the_title(); ?>
                                </b>
                                <span>
                                <?php the_field('question_t'); ?>
                            </span>
                            </h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="display_battle">
                        <div class="row align-items-center contenders-containers">
                            <div class="col-5 link-contender contender_1">
                                <?= formatContenderHtml($id_tournoi, $id_c_1, 1) ?>
                            </div>
                            <div class="col-2">
                                <h4 class="text-center versus">
                                    VS
                                </h4>
                            </div>
                            <div class="col-5 link-contender contender_2">
                                <?= formatContenderHtml($id_tournoi, $id_c_2, 2) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>


<?php if(isset($_GET['classement']) && $_GET['classement'] == "show") : ?>

    <div class="cta">

        <a href="<?php the_permalink(); ?>">
            Retourner au tournoi
        </a>

    </div>


<?php else: ?>

    <div class="cta">

        <div class="display_users_votes">
            <h6>
                <?php if ($nb_user_votes == 0) : ?>
                    Aucun vote encore
                <?php elseif ($nb_user_votes == 1) : ?>
                    Bravo pour ton 1er vote
                <?php else : ?>
                    Vos votes : <?php echo $all_user_votes->post_count; ?>
                <?php endif; ?>
            </h6>
        </div>

        <a href="<?php the_permalink(); ?>?classement=show">
            Voir le classement actuel
        </a>

    </div>

<?php endif; ?>

<?php get_footer(); ?>