<?php
function do_user_ranking( $id_tournament, $id_winner, $id_looser ) {
	$id_classement_user = get_or_create_ranking_if_not_exists( $id_tournament );
	return get_next_duel( $id_classement_user, $id_tournament, $id_winner, $id_looser );
}

function get_next_duel( $id_classement_user, $id_tournament, $id_winner = 0, $id_looser = 0 ) {
	$nb_step                 = 5;
	$next_duel               = [];
	$current_date            = date( 'd/m/Y H:i:s' );
	$list_contenders_tournoi = [];
	$deja_sup_to             = [];
    $deja_inf_to             = [];
    $sum_vote                = 0;
    $timeline                = 0;
    $key_gagnant             = 0;
    $key_perdant             = 0;
    $is_next_duel            = true;
    $c_at_same_place         = [];

	if ( ! $id_classement_user || empty( get_field( 'ranking_r', $id_classement_user ) ) ) {

		// On boucle sur tous les participants du tournoi
        $contenders = new WP_Query(
            array(
                'post_type'      => 'contender',
                'posts_per_page' => -1,
                'meta_key'       => 'ELO_c',
                'orderby'        => 'meta_value_num',
                'order'          => 'DESC',
                'meta_query'     => array(
                    array(
                        'key'     => 'id_tournoi_c',
                        'value'   => $id_tournament,
                        'compare' => 'LIKE',
                    )
                )
            )
        );
		$i          = 0;
		while ( $contenders->have_posts() ) : $contenders->the_post();

			// On créé le tableau avec tous les participants
			// On initialise : place => 0 // Supérieur => vide
			array_push( $list_contenders_tournoi, array(
                "id"                => $i,
                "id_global"         => get_the_ID(),
                "elo"               => get_field('ELO_c'),
                "contender_name"    => get_the_title(),
                "vote"              => 0,
                "superieur_to"      => array(),
                "inferior_to"       => array(),
                "place"             => 0
            ));

			$i ++;
		endwhile;

		// On update le champs Rankin du classement de l'utilisateur
		$list_contenders_tournoi = array_filter( $list_contenders_tournoi );
		update_field( 'ranking_r', $list_contenders_tournoi, $id_classement_user );
	}
	else {

		$list_contenders_tournoi = get_field( 'ranking_r', $id_classement_user );

	}

	// Calcul du volume
    $nb_contenders      = count($list_contenders_tournoi);
    $nb_c_php           = $nb_contenders - 1;
    $half               = $nb_contenders / 2;

    // On boucle sur le ranking pour connaître la position dans le tableau du gagnant et du perdant
	if ( $id_winner && $id_looser ) {
		foreach ( $list_contenders_tournoi as $key => $contender ) {
			if ( $contender['id_global'] == $id_winner ) {
				$key_gagnant = $key;
			}
			if ( $contender['id_global'] == $id_looser ) {
				$key_perdant = $key;
			}
		}

        if($id_winner){
            $list_contenders_tournoi[$key_gagnant]['vote']++;
        }
        if($id_looser){
            $list_contenders_tournoi[$key_perdant]['vote']++;
        }

        // On boucle sur le ranking pour connaître tous les participants qui ont l'ID du gagnant dans le tableau de leur paramètre "superieur_to"
        // On stocke dans la variable "$deja_sup_to" la liste des participants(keys) qui ont battu le gagnant
		foreach ( $list_contenders_tournoi as $key => $contender ) {
			if ( in_array( $key_gagnant, $contender['superieur_to'] ) ) {
				array_push( $deja_sup_to, $key );
			}
            if(in_array($key_perdant, $contender['inferior_to'])){
                array_push($deja_inf_to, $key);
            }
		}

        // On ajoute le gagnant dans la liste de ceux qui l'ont déjà battu
		if ( $id_winner ) {
			array_push( $deja_sup_to, $key_gagnant );
		}
        // On ajoute le perdant dans la liste de ceux qui l'ont déjà battu
        if( $id_looser ){
            array_push($deja_inf_to, $key_perdant);
        }

        // On récupère la liste des participants battu par le perdant du duel
		$list_sup_to_l = $list_contenders_tournoi[ $key_perdant ]['superieur_to'];

        // On récupère la liste des participants qui battent par le gagnant du duel
        $list_inf_to_v = $list_contenders_tournoi[$key_gagnant]['inferior_to'];
	}

    // On boucle sur la liste des participant battant le perdant
    // Cela inclus le gagnant du duel + tout ceux qui ont déjà battu ce gagnant
	foreach ( array_unique( $deja_sup_to ) as $k ) {

		// On récupère la liste des participants que ce participant bat
		$to_up_sup_to = $list_contenders_tournoi[ $k ]['superieur_to'];

		// On ajoute à cette liste, l'ID du perdant du duel
		array_push( $to_up_sup_to, $key_perdant );

		// Si il s'agit du gagnant du duel alors on fusionne les deux liste des participants battu par le gagnant et le perdant
		// Puis modifie la liste "superieur_to" du gagnant avec cette nouvelle liste
		// Si c'est un autre participant qui a déjà battu le vainkeurz alors on ajoute juste
		$total_sup_to                                  = array_merge( $list_sup_to_l, $to_up_sup_to );
		$list_contenders_tournoi[ $k ]['superieur_to'] = array_unique( $total_sup_to );

        // On compte le nombre de personne que le participant bat
        $count_sup_of     = count($list_contenders_tournoi[$k]['superieur_to']);
        $count_inf_of     = count($list_contenders_tournoi[$k]['inferior_to']);

        // On modifie la valeur de sa place avec cette nouvelle valeur
        $list_contenders_tournoi[$k]['place']    = $count_sup_of - $count_inf_of;

	}

    // On boucle sur la liste des participant perdant contre le perdant
    // Cela inclus le perdant du duel + tout ceux qui battent déjà ce perdant
    foreach (array_unique($deja_inf_to) as $k){

        // On récupère la liste des participants qui le battent
        $to_up_inf_to = $list_contenders_tournoi[$k]['inferior_to'];

        // On ajoute à cette liste, l'ID du gagnant du duel
        array_push($to_up_inf_to, $key_gagnant);

        // Si il s'agit du perdant du duel alors on fusionne les deux liste des participants qui battent par le gagnant et le perdant
        // Puis modifie la liste "inferior_to" du perdant avec cette nouvelle liste
        $total_inf_to = array_merge($list_inf_to_v, $to_up_inf_to);
        $list_contenders_tournoi[$k]['inferior_to'] = array_unique($total_inf_to);

        // On compte le nombre de personne que le participant bat
        $count_sup_of     = count($list_contenders_tournoi[$k]['superieur_to']);
        $count_inf_of     = count($list_contenders_tournoi[$k]['inferior_to']);

        // On modifie la valeur de sa place avec cette nouvelle valeur
        $list_contenders_tournoi[$k]['place']    = $count_sup_of - $count_inf_of;

    }

    foreach($list_contenders_tournoi as $item){

        $sum_vote         = $sum_vote + $item['vote'];

    }
    $timeline             = $sum_vote / 2;

    // On enregistre la mise à jour du champs "Ranking" du classement en cours
    update_field("ranking_r", $list_contenders_tournoi, $id_classement_user);


    // Génération du duel
    if($timeline == 0){

        $key_c_1 = $nb_c_php;
        $key_c_2 = $half - 1;

        array_push($next_duel, $list_contenders_tournoi[$key_c_1]['id_global']);
        array_push($next_duel, $list_contenders_tournoi[$key_c_2]['id_global']);

    }
    elseif($timeline != 0 && $timeline < $half){

        $key_c_1 = $nb_contenders - $timeline - 1;
        $key_c_2 = $nb_contenders - $half - $timeline - 1;

        array_push($next_duel, $list_contenders_tournoi[$key_c_1]['id_global']);
        array_push($next_duel, $list_contenders_tournoi[$key_c_2]['id_global']);

    }
    elseif($timeline >= $half && $timeline <= ($nb_c_php - 1 + $half)){

        $list_contenders_reverse = array_reverse($list_contenders_tournoi);

        $key_c_1 = $half - ($nb_contenders - $timeline);
        $key_c_2 = $half - ($nb_contenders - $timeline) + 1;

        array_push($next_duel, $list_contenders_reverse[$key_c_1]['id_global']);
        array_push($next_duel, $list_contenders_reverse[$key_c_2]['id_global']);

    }
    else{

        // On inverse le tableau pour débuter avec les plus faibles
        $list_contenders_reverse = array_reverse($list_contenders_tournoi);

        // On lance des boucles jusqu'à obtenir le tableau "$next_duel" avec deux valeurs
        // On lance autant de boucle que de participant-1
        for($s = 0; $s <= $nb_contenders-1; $s++){

            // Si le tableau "$next_duel" est supérieur ou égal à deux valeurs alors on stop car nous pouvons faire un nouveau duel
            // Sinon on le remet à zéro
            if(count($c_at_same_place) >= 2){
                $step_number = $s;
                break;
            }
            else{
                $c_at_same_place = array();
            }

            // On boucle sur tous les participant et on stocke leur ID global quand leur place est égal à l'incrémentation
            foreach ($list_contenders_reverse as $d => $val){

                if($val['place'] == $s){
                    array_push($c_at_same_place, $val['id_global']);
                }

            }

        }

        $clear_c_at_same_place = array_filter($c_at_same_place);

        if(count($clear_c_at_same_place) >= 2){
            $is_next_duel = true;
            array_push($next_duel, $clear_c_at_same_place[0]);
            array_push($next_duel, $clear_c_at_same_place[1]);
        }
        else{
            $is_next_duel = false;
            if(!get_field('done_r', $id_classement_user)){
                update_field('done_r', 'done', $id_classement_user);
                update_field('done_date_r', date('d/m/Y'), $id_classement_user);
            }
        }

    }

    $contender_1 = $next_duel[0];
    $contender_2 = $next_duel[1];

    var_dump('Half: '.$half.'<br>');
    var_dump('NB contenders: '.$nb_contenders.'<br>');
    var_dump('C1: '.$contender_1.' - '.$key_c_1.'<br>');
    var_dump('C2: '.$contender_2.' - '.$key_c_2.'<br>');
    var_dump('ID Classement: '.$id_classement_user.'<br>');
    var_dump('NB votes: '.$sum_vote.'<br>');
    var_dump('Timeline: '.$timeline.'<br>');
    var_dump('Vote restant ? : '.$is_next_duel.'<br>');
    print_r($list_contenders_tournoi);
    print_r($clear_c_at_same_place);
    print_r($next_duel);


    // On en déduits le nombre d'étapes
	$stade_steps = floor( $nb_contenders / $nb_step );
	if ( isset( $step_number ) ) {

		for ( $m = 1; $m <= $nb_step; $m ++ ) {

			if ( $step_number == 0 ) {
				$current_step = "Début du tournoi";
				$body_class   = "debut_tournoi";
				$bar_step     = "debut_tournoi_bar";
				break;
			} elseif ( $step_number <= $stade_steps * $m ) {
				$current_step = "Étape " . $m . " / " . $nb_step;
				$body_class   = "step_" . $m;
				$bar_step     = "step_bar_" . $m;
				break;
			} else {
				$current_step = "Duel final";
				$body_class   = "fin_tournoi";
				$bar_step     = "fin_tournoi_bar";
			}
		}

	} else {
		$current_step = "Début du tournoi";
	}


	$all_votes_counts = all_votes_in_tournament( $id_tournament );
	$nb_user_votes    = all_user_votes_in_tournament( $id_tournament );

	return compact(
		'is_next_duel',
		'contender_1',
		'contender_2',
		'current_step',
		'body_class',
		'bar_step',
		'all_votes_counts',
		'nb_user_votes',
		'nb_contenders',
		'id_tournament'
	);

}

/**
 * Créer un classement pour associer à l'utilisateur et au tournoi si il n'existe pas
 *
 * @param int $id_tournament
 *
 * @return bool|false|int|WP_Error $id_classment_user
 */
function get_or_create_ranking_if_not_exists( $id_tournament ) {
	$classement_perso = new WP_Query( array(
		'post_type'      => 'classement',
		'posts_per_page' => '1',
		'meta_query'     =>
			array(
				'relation' => 'AND',
				array(
					'key'     => 'id_tournoi_r',
					'value'   => $id_tournament,
					'compare' => '=',
				),
				array(
					'key'     => 'uuid_user_r',
					'value'   => $_COOKIE['vainkeurz_user_id'],
					'compare' => '=',
				)
			)
	) );
	if ( $classement_perso->have_posts() ) {
		while ( $classement_perso->have_posts() ) : $classement_perso->the_post();
			$id_classement_user = get_the_ID();
		endwhile;
		wp_reset_postdata();
	} else {
		$new_classement     = array(
			'post_type'   => 'classement',
			'post_title'  => 'T:' . $id_tournament . ' U:' . $_COOKIE['vainkeurz_user_id'],
			'post_status' => 'publish',
		);
		$id_classement_user = wp_insert_post( $new_classement );
		update_field( 'uuid_user_r', $_COOKIE['vainkeurz_user_id'], $id_classement_user );
		update_field( 'id_tournoi_r', $id_tournament, $id_classement_user );
	}

	return $id_classement_user;

}

// All total votes in the current tournoi by the current user
function all_user_votes_in_tournament( $id_tournament ) {

	$all_user_votes = new WP_Query( array(
		'post_type'      => 'vote',
		'posts_per_page' => - 1,
		'meta_query'     => array(
			'relation' => 'AND',
			array(
				'key'     => 'id_t_v',
				'value'   => $id_tournament,
				'compare' => '=',
			),
			array(
				'key'     => 'id_user_v',
				'value'   => $_COOKIE['vainkeurz_user_id'],
				'compare' => '=',
			)
		)
	) );

	return $all_user_votes->found_posts;
}

// All total votes in the current tournoi
function all_votes_in_tournament( $id_tournament ) {

	$all_votes = new WP_Query( array(
		'post_type'      => 'vote',
		'posts_per_page' => - 1,
		'meta_query'     => array(
			array(
				'key'     => 'id_t_v',
				'value'   => $id_tournament,
				'compare' => '=',
			)
		)
	) );

	return $all_votes->found_posts;
}


function genrerate_tournament_response($tournament_infos){
	extract($tournament_infos);
	ob_start();
	$id_tournoi = $id_tournament;
	set_query_var( 'battle_vars', compact( 'contender_1', 'contender_2', 'id_tournoi', 'all_votes_counts' ) );
	get_template_part( 'templates/parts/content', 'battle' );

	$contenders_html = ob_get_clean();

	ob_start();
	set_query_var( 'steps_var', compact( 'bar_step', 'current_step' ) );
	get_template_part( 'templates/parts/content', 'step-bar' );
	$stepbar_html = ob_get_clean();

	ob_start();
	set_query_var( 'user_votes_vars', compact( 'nb_user_votes' ) );
	get_template_part( 'templates/parts/content', 'user-votes' );
	$uservotes_html = ob_get_clean();

	return die(json_encode( array(
		'stepbar_html' => $stepbar_html,
		'contenders_html' => $contenders_html,
		'uservotes_html' => $uservotes_html,
		'all_votes_counts' => $all_votes_counts,
		'is_next_duel' => $is_next_duel
	) ));
}
