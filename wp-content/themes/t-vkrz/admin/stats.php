<?php
/*
    Template Name: Statistiques
*/
?>
<?php get_header(); ?>

<?php
    $id_tournoi      = $_GET['idtournoi'];
    $list_votes      = array();

    $all_votes       = new WP_Query(array(
        'post_type'      => 'vote',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => 'id_t_v',
                'value'   => $id_tournoi,
                'compare' => '=',
            )
        )
    ));
    
    while ($all_votes->have_posts()) : $all_votes->the_post();
    
        array_push($list_votes, get_field('id_user_v'));
    
    endwhile;
    
    $nb_all_votes       = $all_votes->post_count;
    $clean_list_votes   = array_unique($list_votes);
    $nb_votants         = count($clean_list_votes);
    $moyenne_vote_user  = floor($nb_all_votes / $nb_votants);

    $count_uniqID       = array_count_values($list_votes);
    asort($count_uniqID);
    $reverse_count_uniqID = array_reverse($count_uniqID);
    $it=1; foreach ($count_uniqID as $key => $value){
        if($it == 1){
            $id_smallest_voteur  = $key;
            $id_less_votes       = $value;
        }
        $it++;
    }
    $it=1; foreach ($reverse_count_uniqID as $key => $value){
        if($it == 1){
            $id_biggest_voteur  = $key;
            $id_most_votes      = $value;
        }
        $it++;
    }
?>

<div class="container">
    <div class="row">
        <div class="col">
            <ul>
                <li>
                    Nombre de votes : <b><?php echo $nb_all_votes; ?></b>
                </li>
                <li>
                    Nombre de votants : <b><?php echo $nb_votants; ?></b>
                </li>
                <li>
                    Moyenne de votes par user : <b><?php echo $moyenne_vote_user; ?></b>
                </li>
                <li>
                    Plus grande série : <b><?php echo $id_most_votes; ?></b>
                </li>
                <li>
                    Plus petite série : <b><?php echo $id_less_votes; ?></b>
                </li>
            </ul>
        </div>
    </div>
</div>


<?php get_footer(); ?>
