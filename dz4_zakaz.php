<?php
header('Content-type: text/html; charset=utf-8');
/*
 * 
 * - Вам нужно вывести корзину для покупателя, где указать: 
 * 1) Перечень заказанных товаров, их цену, кол-во и остаток на складе
 * 2) В секции ИТОГО должно быть указано: сколько всего наименовний было заказано, каково общее количество товара, какова общая сумма заказа
 * - Вам нужно сделать секцию "Уведомления", где необходимо извещать покупателя о том, что нужного количества товара не оказалось на складе
 * - Вам нужно сделать секцию "Скидки", где известить покупателя о том, что если он заказал "игрушка детская велосипед" в количестве >=3 штук, то на эту позицию ему 
 * автоматически дается скидка 30% (соответственно цены в корзине пересчитываются тоже автоматически)
 * 3) у каждого товара есть автоматически генерируемый скидочный купон diskont, используйте переменную функцию, чтобы делать скидку на итоговую цену в корзине
 * diskont0 = скидок нет, diskont1 = 10%, diskont2 = 20%
 * 
 * В коде должно быть использовано:
 * - не менее одной функции
 * - не менее одного параметра для функции
 * операторы if, else, switch
 * статические и глобальные переменные в теле функции
 * 

 */

$ini_string='
[игрушка мягкая мишка белый]
цена = '.  mt_rand(1, 10).';
количество заказано = '.  mt_rand(1, 10).';
осталось на складе = '.  mt_rand(0, 10).';
diskont = diskont'.  mt_rand(0, 2).';
    
[одежда детская куртка синяя синтепон]
цена = '.  mt_rand(1, 10).';
количество заказано = '.  mt_rand(1, 10).';
осталось на складе = '.  mt_rand(0, 10).';
diskont = diskont'.  mt_rand(0, 2).';
    
[игрушка детская велосипед]
цена = '.  mt_rand(1, 10).';
количество заказано = '.  mt_rand(1, 10).';
осталось на складе = '.  mt_rand(0, 10).';
diskont = diskont'.  mt_rand(0, 2).';
';
$bd = parse_ini_string($ini_string, true);
//print_r($bd);
?>
<html>
    <head>
        <title> Корзина </title>
    </head>
    <body>
        <h2>КОРЗИНА ПОКУПАТЕЛЯ</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>Вы хотите <br> приобрести товар</th>
                    <th>Заказываемое количество</th>
                    <th>Доступное на складе <br> количество, (шт.)</th>
                    <th>Ваш заказ <br> с учетом кол-ва на складе</th>
                    <th>Цена за <br> единицу товара (руб.)</th>
                    <th>Скидка по <br> Вашему купону</th>
                    <th>Цена за <br> единицу товара <br> с учетом скидки (руб.)</th>
                    <th>Общая стоимость <br> товара по наличию <br> с учетом скидки (руб.)</th>
                </tr>
            </thead>
            <tbody>
<?php
$result = array();
function parse_basket($basket){  // функция парсер корзины
global $result;
// разбираем масси по-элементно, и посмотреть все элементы и все их параметры, для этого пользуемся foreach:
    foreach($basket as $name => $params){
        // это звучит так: для каждого элемента форич разбей мне на составляющие:
        // ключи помести в $name, а значения в $params
        
        $discount = discount($params['цена'],$params['количество заказано'],$params['diskont'],$name);
        $available = available($params['осталось на складе'],$params['количество заказано'],$name);
        
        // print_r($params); // посмотрим, что получается
        
        //     вывод таблицы
    echo "<tr>
        <td>$name</td>;
        <td> " .$params['количество заказано']." </td>
        <td> " .$params['осталось на складе']." </td>
        <td> " .$available['available_amount']." </td>
        <td> " .$params['цена']." </td>
        <td> " .$discount['skidka']." </td>
        <td> " .$discount['price']." </td>
        <td> " .$discount['price_total']." </td>
          </tr>";
    $result['количество заказано'] += $params['количество заказано'];
    $result['available_amount'] += $available['available_amount'];
    $result['price_total'] += $discount['price_total'];
    }
}
  
$note = array();
$share_on_bike = array();
function available($stock,$amount,$name){
    global $note;
    if($stock >= $amount){
    $available_amount = $amount;  
    }else{
        $available_amount = $stock;
        $note[]='Товар '.$name.': нехватка на складе : '.($amount-$stock).'шт.';
        }
    return array('available_amount' => $available_amount);
}

function discount($price,$amount,$diskont,$name){
    global $share_on_bike;
    $skidka = substr($diskont,7,2);
    if(($name == 'игрушка детская велосипед') && $amount >= 3){
         $skidka = 3;
         $share_on_bike[]=' Поздравляем! Вы заказали товар '.$name.
                ' в количестве '.$amount.'шт, '.
                ' согласно условий акции, Вам предоставляется скидка 30%';
    }
    $price_width_diskont_per_item = $price - ($price * ($skidka * 10) / 100);
    $total_price_all_items_width_diskont = $price_width_diskont_per_item * $amount;
                
    return array('skidka' => $skidka."0%",
                 'price' => $price_width_diskont_per_item,
                 'price_total' =>$total_price_all_items_width_diskont
                );
}

parse_basket($bd);  // это своеобразная точка входа в сам процесс разработки (разбиваем всё на логически блоки

?>

        </tbody>
        </table>
        
        <hr>

    <tr>
        <td>Итого заказано наименований: <?=count($bd)?><br>
            Итого заказано количество: <?=$result['количество заказано']?><br>
            Итого количество доступно к заказу: <?=$result['available_amount']?><br>
            Общая стоимость заказа: <?=$result['price_total']?></td>
    </tr>
    <hr>
    <tr>
        <td colspan="5">Уведомления:<br> <?=join("<br>",$note)?></td>
        
    </tr>
    <hr>
    <tr>
        <td colspan="20"><br> <?=join("<br>",$share_on_bike)?></td>
    </tr>
</body>
</html>
