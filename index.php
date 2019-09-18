<?php
    function insert_sushi($pdo, $name){
        $pdo->query("insert into orders (name) values ('".$name."')");
        echo $name."を注文しました。";
    }

    function eat_sushi($pdo, $eat_id){
        $pdo->query("update orders set ate = true where id=".$eat_id);
        echo $pdo->query('select name from orders where id='.$eat_id)->fetchColumn()."を食べました。";
    }

    function random_spawn($pdo, $size=8){
        $sushi_count = $pdo->query('select count(*) from orders')->fetchColumn();
        if($sushi_count > $size){
            $sushi_ids = range(1, $sushi_count);
            shuffle($sushi_ids);
            $sushi_ids = array_slice($sushi_ids, 0, $size);
            $pdo->query("update orders set ate = false where id in (".join(',', $sushi_ids).")");
        } else {
            $pdo->query("update orders set ate = false");
        }
    }
    
    $pdo = new PDO("pgsql:dbname=sushidb", "sushier", "sushi");
    
    if(isset($_POST['order'])){
        $order_text = $_POST['order'];
        insert_sushi($pdo, $order_text);
    } else if(isset($_POST['eat_id'])){
        $eat_id = $_POST['eat_id'];
        eat_sushi($pdo, $eat_id);
    }
    if($pdo->query('select count(*) from orders where ate=false')->fetchColumn() == 0){
        random_spawn($pdo);
    }
    $sushi_list = $pdo->query('select * from orders where ate=false')->fetchAll(PDO::FETCH_CLASS);
?>

<html>
    <head>
        <title>Sushi</title>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>
    <body>
        <h1>寿司</h1>
        <div>
            <?php
                foreach($sushi_list as $sushi){
                    echo "<form method='post' name='eat_".$sushi->id."' action='/'>";
                    echo "<input type='hidden' name='eat_id' value='".$sushi->id."'>";
                    echo "</form>";
                }
            ?>
        </div>
        <div class="marquee">
            <p>
                <?php
                    foreach($sushi_list as $sushi){
                        echo "<span class='sushi'>";
                        echo "<a href='javascript:eat_".$sushi->id.".submit()'>".$sushi->name."</a>";
                        echo "</span>";
                    }
                ?>
            </p>
        </div>
        <form action="index.php" method="post">
            <input type="text" name="order">
            <input type="submit" value="注文">
        </form>
        <?php
        ?>
    </body>
</html>