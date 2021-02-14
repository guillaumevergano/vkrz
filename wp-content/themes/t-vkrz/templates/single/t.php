<?php
$id_tournoi              = get_the_ID();
$uuiduser                = $_COOKIE["vainkeurz_user_id"];

$nb_user_votes  = all_user_votes_in_tournament($id_tournoi);

$id_classement_user = get_or_create_ranking_if_not_exists($id_tournoi);
extract(get_next_duel($id_classement_user, $id_tournoi));

wp_reset_query();

if ( get_field( 'cover_t' ) ) {
    $illu     = wp_get_attachment_image_src( get_field( 'cover_t' ), 'full' );
    $illu_url = $illu[0];
}
?>
<?php get_header(); ?>

<body <?php body_class( array( 'cover', 'a_step', $body_class ) ); ?> style="background: url(<?php echo $illu_url; ?>) center center no-repeat">

<div class="main">

    <header class="header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-4">
                    <div class="logo">
                        <a href="<?php bloginfo( 'url' ); ?>/">
                            <img src="<?php bloginfo( 'template_directory' ); ?>/assets/img/logo-vainkeurz.png" alt=""
                                 class="img-fluid">
                        </a>
                    </div>
                </div>
                <div class="col-sm-8 text-right">
                    <div class="display_users_votes">
                        <a href="https://baltazare1.typeform.com/to/j9n8JU" target="_blank" class="cta_2">
                            ☝️ Donnez nous votre avis !
                        </a>
                        <?php
                        set_query_var( 'user_votes_vars', compact( 'nb_user_votes' ) );
                        get_template_part( 'templates/parts/content', 'user-votes' );
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </header>
	<?php if ( $is_next_duel ): ?>
        <div class="container">
            <div class="tournoi_infos">
                <div class="row align-items-center">
                    <div class="col-12">
                        <div class="bloc-titre">
                            <h1>
                                <b>
									<?php the_title(); ?>
                                </b>
                            </h1>
                            <h2>
								<?php the_field( 'question_t' ); ?>
                                <span class="toshowpopover moreinfo" data-container="body" data-toggle="popover"
                                      data-placement="top" data-content="<?php the_field( 'precision_t' ); ?>">
                                <i class="fal fa-info-circle"></i>
                            </span>
                            </h2>
                            <ul class="infos_tournoi">
                                <li class="toshowpopover" data-container="body" data-toggle="popover"
                                    data-placement="top"
                                    data-content="<?php echo $nb_contenders; ?> participants dans ce tournoi">
                                    <i class="fad fa-users-crown"></i> <?php echo $nb_contenders; ?>
                                </li>
                                <li class="toshowpopover" data-container="body" data-toggle="popover"
                                    data-placement="top"
                                    data-content="Vous devez voter environ <?php echo $nb_contenders * 3; ?> fois pour finir votre classement">
                                    <i class="fad fa-infinity"></i> <?php echo $nb_contenders * 3; ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="display_battle">
						<?php
						    set_query_var( 'battle_vars', compact( 'contender_1', 'contender_2', 'id_tournoi', 'all_votes_counts' ) );
						    get_template_part( 'templates/parts/content', 'battle' );
						?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        set_query_var( 'steps_var', compact( 'bar_step', 'current_step' ) );
        get_template_part( 'templates/parts/content', 'step-bar' );
        ?>
	<?php else: ?>
        <div class="finish_tournoi">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h2 class="text-center">
                            <i class="fal fa-badge-check"></i>
                            <br>
                            Félicitations,
                            <br>vous avez terminé ce tournoi
                        </h2>
                        <div class="more_links text-center">
                            <ul class="list-unstyled list-inline">
                                <li>
                                    <a href="<?php the_permalink( $id_classement_user ); ?>" class="cta_2">
                                        Voir votre classement
                                    </a>
                                </li>
                                <br>
                                <li>
                                    <a href="<?php the_permalink(get_page_by_path('classement-elo')); ?>?id_tournoi=<?php echo $id_tournoi; ?>" target="_blank" class="cta-1 cta_btn">
                                        Voir le classement global
                                    </a>
                                </li>
                            </ul>
                            <div>
                                <a href="<?php bloginfo( 'url' ); ?>/" class="cta-2 cta_btn">
                                    Retourner aux tournois
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
