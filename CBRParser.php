<?php

class CBRParser
{
    // Результирующая функция вывода валюты на выбранную дату
    public function getCurrency($currency = 'USD', $date = null)
    {
        // Установим даты
        $date = $date ?? date ('d.m.Y');
        $oldDate = $this->getOldDate($date);

        // Получим данные по датам и валютам
        $oldValue = $this->getCbrData($currency, $oldDate);
        $currentValue = $this->getCbrData($currency, $date);

        // Приведем данные валют к числам
        $oldValue = floatval($oldValue);
        $currentValue = floatval($currentValue);

        // Вывод валюты и статуса относительно прошлого дня
        if ($oldValue < $currentValue) {
            return print_r('<span class="color: green;">[up]</span>' . "$currency " . $currentValue);
        } else if ($oldValue === $currentValue) {
            return print_r('<span class="color: green;">[==]</span>' . "$currency " . $currentValue);
        }
        else {
            return print_r('<span class="color: red;">[down]</span>' . "$currency " . $currentValue);
        }
    }

    // Функция получения -1 дня от текущей даты
    private function getOldDate($date)
    {
        $date = new \DateTime($date);
        $date->modify('-1 day');

        return $date->format('d.m.Y');
    }

    // Функция парсинга данных с Cbr
    private function getCbrData($currency, $date)
    {
        // Создадим объект для работы XML
        $xml = new DOMDocument();
        // Ссылка на ресурс

        $url = 'http://www.cbr.ru/scripts/XML_daily.asp?date_req=' . $date;
        // получаем общий xml
        if ($xml->load($url)) {
            // Массив для хранения курсов валют
            $result = array();
            // Разбираем xml
            $root = $xml->documentElement;
            // Берем все теги 'Valute' и их содержимое
            $items = $root->getElementsByTagName('Valute');
            // Переберем теги 'Valute' по одному
            foreach ($items as $item) {
                // Получаем код валюты
                $code = $item->getElementsByTagName('CharCode')->item(0)->nodeValue;
                // Получаем значение курса валюты, относительно рубля
                $value = $item->getElementsByTagName('Value')->item(0)->nodeValue;
                // записываем в массив, предварительно заменив запятую на точку
                $result[$code] = str_replace(',', '.', $value);
            }
            // Возвращаем значение курса, для запрошенной валюты
            return $result[$currency];
        } else {
            // Если не получили xml возвращаем false
            return false;
        }
    }
}
