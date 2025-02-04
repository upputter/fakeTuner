<?php

require_once('./stationsList.php'); // load array $stations

$fakeScriptUrl = 'http://sagem.vtuner.com/fakeTuner/index.php';
$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : 'start';

$xmlHeader = <<<XMLHEAD
<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>

XMLHEAD;

echo $xmlHeader;


if (isset($_REQUEST['token'])) {
    echo '<EncryptedToken>0000000000000000</EncryptedToken>';
    exit;
}

switch ($action) {
    case 'start':
        echo '<ListOfItems>
<ItemCount>-1</ItemCount>
<Item>
<ItemType>Dir</ItemType>
<Title>Favoriten</Title>
<UrlDir>' . $fakeScriptUrl . '?action=favs</UrlDir>
<UrlDirBackUp>' . $fakeScriptUrl . '?mac=1</UrlDirBackUp>
<DirCount>1</DirCount>
</Item>
</ListOfItems>';
        break;

    case 'favs':
        echo buildXML($stations);
        break;
    }

function buildXML(array $data = [])
{
    $string = '<ListOfItems>
<ItemCount>' . count($data) . '</ItemCount>' . "\n";
    foreach ($data as $station) {
        $string .= '<Item>
<ItemType>Station</ItemType>
<StationId>FAV_'. md5($station['title']) .'</StationId>
<StationName>' . $station['title'] . '</StationName>
<StationUrl>' . $station['url'] . '</StationUrl>
<StationDesc>Klassik</StationDesc>
<StationFormat>Klassik</StationFormat>
<StationBandWidth>None</StationBandWidth>
<StationMime>MP3</StationMime>
<Relia>3</Relia>
</Item>' . "\n";
    }

    $string .= '</ListOfItems>';
    return $string;
}
