<?php

class ZoneController
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
        header("Content-Type: application/json");
    }

    // GET all active zones
public function getAllZones()
{
    $query = "SELECT *, ST_AsText(coordinates) AS coordinates_text FROM zones WHERE status = 1";
    $result = $this->conn->query($query);

    $zones = [];

    while ($row = $result->fetch_assoc()) {
        $polygon = [];
        $formattedCoordinates = [];

        // Parse POLYGON text
        if (!empty($row['coordinates_text']) && preg_match('/\(\((.*?)\)\)/', $row['coordinates_text'], $matches)) {
            $points = explode(',', $matches[1]);
            foreach ($points as $point) {
                [$lng, $lat] = preg_split('/\s+/', trim($point));
                $lng = floatval($lng);
                $lat = floatval($lat);
                $polygon[] = [$lng, $lat];
                $formattedCoordinates[] = ['lat' => $lat, 'lng' => $lng];
            }
        }

        // Fetch translations
        $zoneId = intval($row["id"]);
        $translations = [];
        $transQuery = "SELECT * FROM translations WHERE translationable_type = 'App\\\\Models\\\\Zone' AND translationable_id = ?";
        $stmt = $this->conn->prepare($transQuery);
        $stmt->bind_param("i", $zoneId);
        $stmt->execute();
        $transResult = $stmt->get_result();
        while ($transRow = $transResult->fetch_assoc()) {
            $translations[] = [
                "id" => intval($transRow["id"]),
                "translationable_type" => $transRow["translationable_type"],
                "translationable_id" => intval($transRow["translationable_id"]),
                "locale" => $transRow["locale"],
                "key" => $transRow["key"],
                "value" => $transRow["value"],
                "created_at" => $transRow["created_at"] ?? null,
                "updated_at" => $transRow["updated_at"] ?? null
            ];
        }

        // Merge zone data
        $zone = [
            "id" => $zoneId,
            "name" => $row["name"],
            "coordinates" => [
                "type" => "Polygon",
                "coordinates" => [$polygon]
            ],
            "status" => intval($row["status"]),
            "created_at" => $row["created_at"],
            "updated_at" => $row["updated_at"],
            "store_wise_topic" => $row["store_wise_topic"],
            "customer_wise_topic" => $row["customer_wise_topic"],
            "deliveryman_wise_topic" => $row["deliveryman_wise_topic"],
            "cash_on_delivery" => boolval($row["cash_on_delivery"]),
            "digital_payment" => boolval($row["digital_payment"]),
            "increased_delivery_fee" => floatval($row["increased_delivery_fee"]),
            "increased_delivery_fee_status" => intval($row["increased_delivery_fee_status"]),
            "increase_delivery_charge_message" => $row["increase_delivery_charge_message"],
            "offline_payment" => boolval($row["offline_payment"]),
            "display_name" => $row["display_name"],
            "formated_coordinates" => $formattedCoordinates,
            "translations" => $translations
        ];

        $zones[] = $zone;
    }

    echo json_encode($zones);
    exit;
}




    public function zonesCheck()
{
    $lat = floatval($_GET['lat'] ?? 0);
    $lng = floatval($_GET['lng'] ?? 0);
    $zone_id = intval($_GET['zone_id'] ?? 0);

    if (!$lat || !$lng || !$zone_id) {
        echo json_encode([
            "status" => false,
            "message" => "Missing lat, lng, or zone_id"
        ]);
        exit;
    }

    // Get raw polygon from DB
    $sql = "SELECT ST_AsText(coordinates) AS polygon FROM zones WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $zone_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row || empty($row['polygon'])) {
        echo json_encode(["status" => false, "message" => "Zone not found"]);
        exit;
    }

    $polygonText = $row['polygon']; // Example: POLYGON((lng lat, lng lat, ...))
    
    // Properly parse polygon text
    $polygon = [];
    if (preg_match('/POLYGON\(\((.*)\)\)/', $polygonText, $matches)) {
        $points = explode(',', $matches[1]);
        foreach ($points as $point) {
            $coords = preg_split('/\s+/', trim($point));
            if (count($coords) == 2) {
                $polygon[] = ['lng' => floatval($coords[0]), 'lat' => floatval($coords[1])];
            }
        }
    }

    if (count($polygon) < 3) {
        echo json_encode(["status" => false, "message" => "Invalid polygon format"]);
        exit;
    }

    // Use point-in-polygon algorithm
    $inside = $this->pointInPolygon($lat, $lng, $polygon);

    echo json_encode([
        "status" => true,
        "in_zone" => $inside,
        "point" => ["lat" => $lat, "lng" => $lng],
        // "polygon_count" => count($polygon),
        // "first_polygon_point" => $polygon[0],
        // "last_polygon_point" => end($polygon)
    ]);
    exit;
}



private function pointInPolygon($lat, $lng, $polygon)
{
    $inside = false;
    $j = count($polygon) - 1;

    for ($i = 0; $i < count($polygon); $i++) {
        $xi = $polygon[$i]['lng'];
        $yi = $polygon[$i]['lat'];
        $xj = $polygon[$j]['lng'];
        $yj = $polygon[$j]['lat'];

        // Check if point lies exactly on a vertex or edge
        if (
            $this->isPointOnLine($lat, $lng, $yi, $xi, $yj, $xj)
        ) {
            return true;
        }

        $intersect = (($yi > $lat) != ($yj > $lat)) &&
            ($lng < ($xj - $xi) * ($lat - $yi) / ($yj - $yi + 0.000000001) + $xi);
        if ($intersect) {
            $inside = !$inside;
        }

        $j = $i;
    }

    return $inside;
}

private function isPointOnLine($px, $py, $x1, $y1, $x2, $y2, $epsilon = 0.0000001)
{
    // Convert to double precision floats
    $d1 = sqrt(pow($px - $x1, 2) + pow($py - $y1, 2));
    $d2 = sqrt(pow($px - $x2, 2) + pow($py - $y2, 2));
    $lineLen = sqrt(pow($x2 - $x1, 2) + pow($y2 - $y1, 2));

    return abs(($d1 + $d2) - $lineLen) < $epsilon;
}

}
