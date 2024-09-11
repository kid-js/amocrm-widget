<?php
$api_request = file_get_contents('php://input');
// $api_request = json_decode(file_get_contents('php://input'));
$subdomain = 'ovalm8'; //Поддомен нужного аккаунта
$link = 'https://' . $subdomain . '.amocrm.ru' . $api_request; //Формируем URL для запроса
// $link = 'https://' . $subdomain . '.amocrm.ru/api/v4/tasks?limit=3'; //Формируем URL для запроса
// $link = 'https://' . $subdomain . '.amocrm.ru/api/v4/leads?limit=3'; //Формируем URL для запроса
// $link = 'https://' . $subdomain . '.amocrm.ru/api/v4/tasks/25959257'; //Формируем URL для запроса
// $link = 'https://' . $subdomain . '.amocrm.ru/api/v4/tasks/custom_fields'; //Формируем URL для запроса
/** Получаем access_token из вашего хранилища */
$access_token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImYyYmQ0ODYwMDI3M2M0NGE0YWM2ZmE3MWZmYjY3MzA0MjUzNDk1ZGUxZDk4YWVkOWY0MzBlYmIyMTFiNjJkYmEzNTg3OGIxMjgxODNlMzk2In0.eyJhdWQiOiIxMmY4YzBlZi0yN2YyLTRmZjQtYmJiMi0wMjQ3ZjM2ZTk4NzYiLCJqdGkiOiJmMmJkNDg2MDAyNzNjNDRhNGFjNmZhNzFmZmI2NzMwNDI1MzQ5NWRlMWQ5OGFlZDlmNDMwZWJiMjExYjYyZGJhMzU4NzhiMTI4MTgzZTM5NiIsImlhdCI6MTcyNjA1NzU0MCwibmJmIjoxNzI2MDU3NTQwLCJleHAiOjE3Mjc2NTQ0MDAsInN1YiI6IjExNDkwNjIyIiwiZ3JhbnRfdHlwZSI6IiIsImFjY291bnRfaWQiOjMxOTM4NjMwLCJiYXNlX2RvbWFpbiI6ImFtb2NybS5ydSIsInZlcnNpb24iOjIsInNjb3BlcyI6WyJjcm0iLCJmaWxlcyIsImZpbGVzX2RlbGV0ZSIsIm5vdGlmaWNhdGlvbnMiLCJwdXNoX25vdGlmaWNhdGlvbnMiXSwiaGFzaF91dWlkIjoiMzJmMWQwNDEtNjkzNi00MzMzLTg3N2UtMmNiZTM0M2RhZmUxIiwiYXBpX2RvbWFpbiI6ImFwaS1iLmFtb2NybS5ydSJ9.KBLnfFjGI9e9l_XyOmxyKRh1-engXTV-aefHX-6jXqUtpXx1MyN-R607a4RsZEnSTAyuV2g3JZnuF1qjxAvLAhCXmYX1282rfqOcTHNx2sDZ0RF2zXmXFET7W_RR1l3NinCov3LuhdQ_dXPzsYGhIxTc4Bl_0fc4bP99kUNrVlGcEeUdfd-_yEzUY4CS5mkk4Zd4cBk7DPDl-phRyIXaEaUHhqbbtu5BMQBPah2ktAunNDLk1ANTc_U8SxmZgXV9rhCF4rSDY8Q0egkcLnUbPcqBctd-1bH4FQJ5yxTb11rwdzQD2pJCxlcA7dEjubjYdR0ZuAMzaFrdUJEBXgHIwg';
/** Формируем заголовки */
$headers = [
    'Authorization: Bearer ' . $access_token
];
/**
 * Нам необходимо инициировать запрос к серверу.
 * Воспользуемся библиотекой cURL (поставляется в составе PHP).
 * Вы также можете использовать и кроссплатформенную программу cURL, если вы не программируете на PHP.
 */
$curl = curl_init(); //Сохраняем дескриптор сеанса cURL
/** Устанавливаем необходимые опции для сеанса cURL  */
curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
curl_setopt($curl,CURLOPT_URL, $link);
curl_setopt($curl,CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl,CURLOPT_HEADER, false);
curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
$out = curl_exec($curl); //Инициируем запрос к API и сохраняем ответ в переменную
$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);
/** Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом. */
$code = (int)$code;
$errors = [
    400 => 'Bad request',
    401 => 'Unauthorized',
    403 => 'Forbidden',
    404 => 'Not found',
    500 => 'Internal server error',
    502 => 'Bad gateway',
    503 => 'Service unavailable',
];

try
{
    /** Если код ответа не успешный - возвращаем сообщение об ошибке  */
    if ($code < 200 || $code > 204) {
        throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
    }
} catch(\Exception $e)
{
    die('Ошибка: ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode());
}

// echo json_encode($out);
echo $out;
// echo json_encode($api_request);


