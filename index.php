<?php

$array = [
    ['id' => 1, 'date' => "12.01.2020", 'name' => "test1"],
    ['id' => 2, 'date' => "02.05.2020", 'name' => "test2"],
    ['id' => 4, 'date' => "08.03.2020", 'name' => "test4"],
    ['id' => 1, 'date' => "22.01.2020", 'name' => "test1"],
    ['id' => 2, 'date' => "11.11.2020", 'name' => "test4"],
    ['id' => 3, 'date' => "06.06.2020", 'name' => "test3"]
];


// 1. выделить уникальные записи (убрать дубли) в отдельный массив. в конечном массиве не должно быть элементов с одинаковым id.
function getUniqueRecords($array)
{
    // Extract unique entries based on the 'id' field
    $uniqueArray = array_column($array, null, 'id');

    $uniqueArray = array_values($uniqueArray);
    return $uniqueArray;
}
// 2. отсортировать многомерный массив по ключу (любому)
function arraySort($array)
{
    $names = array_column($array, 'name');
    $indexes = array_keys($array);

    array_multisort($names, SORT_ASC, $indexes, $array);

    return $array;
}

//3. вернуть из массива только элементы, удовлетворяющие внешним условиям (например элементы с определенным id)
function filterArray($array, $condition)
{
    return array_filter($array, function ($item) use ($condition) {

        return $item['id'] == $condition;
    });
}

// 4. изменить в массиве значения и ключи (использовать name => id в качестве пары ключ => значение)
function modifyArray($array)
{
    $modifiedArray = array_reduce(
        array_column($array, 'name'),
        function ($result, $name) use ($array) {
            $id = array_column($array, 'id', 'name')[$name];
            $result[$name] = $id;
            return $result;
        },
        []
    );

    return $modifiedArray;
}

// 5. В базе данных имеется таблица с товарами goods (id INTEGER, name TEXT), таблица с тегами tags (id INTEGER, name TEXT) и таблица связи товаров и тегов goods_tags (tag_id INTEGER, goods_id INTEGER, UNIQUE(tag_id, goods_id)). Выведите id и названия всех товаров, которые имеют все возможные теги в этой базе.
function getGoodsWithAllTags()
{
    $dsn = 'mysql:host=localhost;dbname=database_name;charset=utf8';
    $username = 'username';
    $password = 'password';

    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $query = "
            SELECT g.id, g.name
            FROM goods g
            WHERE (
              SELECT COUNT(DISTINCT t.id)
              FROM tags t
              ) = (
              SELECT COUNT(DISTINCT gt.tag_id)
              FROM goods_tags gt
              )
              AND NOT EXISTS (
                SELECT t.id
                FROM tags t
                WHERE NOT EXISTS (
                  SELECT gt.goods_id
                  FROM goods_tags gt
                  WHERE gt.tag_id = t.id
                  AND gt.goods_id = g.id
                )
              )
        ";

        $statement = $pdo->query($query);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return [];
    }
}

// Usage example
$results = getGoodsWithAllTags();
foreach ($results as $row) {
    echo "ID: " . $row['id'] . ", Name: " . $row['name'] . "<br>";
}


// 6. Выбрать без join-ов и подзапросов все департаменты, в которых есть мужчины, и все они (каждый) поставили высокую оценку (строго выше 5).
function getDepartmentsWithHighlyRatedMen()
{
    $dsn = 'mysql:host=localhost;dbname=database_name;charset=utf8';
    $username = 'username';
    $password = 'password';

    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $query = "
            SELECT e.department_id
            FROM evaluations e
            WHERE e.gender = true
            GROUP BY e.department_id
            HAVING MIN(e.value) > 5
        ";

        $statement = $pdo->query($query);
        $result = $statement->fetchAll(PDO::FETCH_COLUMN);

        return $result;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return [];
    }
}

$departments = getDepartmentsWithHighlyRatedMen();
foreach ($departments as $department) {
    echo "Department ID: " . $department . "<br>";
}
