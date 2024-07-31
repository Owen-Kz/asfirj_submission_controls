<?php
include "../cors.php";

if (isset($_GET['orcid'])) {
    $orcidId = $_GET['orcid'];
    $orcidUrl = "https://pub.orcid.org/v3.0/$orcidId";

    $options = array(
        'http' => array(
            'header' => "Accept: application/json\r\n"
        )
    );

    $context = stream_context_create($options);
    $result = file_get_contents($orcidUrl, false, $context);

    if ($result) {
        $data = json_decode($result, true);
        $userData = array(
            'name' => $data['person']['name']['given-names']['value'] . ' ' . $data['person']['name']['family-name']['value'],
            // Add other fields as needed
        );

        header('Content-Type: application/json');
        echo json_encode($userData);
    } else {
        echo json_encode(array('error' => 'No data found'));
    }
} else {
    echo json_encode(array('error' => 'Invalid ORCID iD'));
}
?>
