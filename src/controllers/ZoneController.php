<?php
require_once __DIR__ . '/../../db.php';

class ZoneController
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    // Active Zones
    public function getAllZones()
    {
        header('Content-Type: application/json');

        $sql = "SELECT * FROM zones WHERE status = 1";
        $result = $this->conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $zones = [];

            while ($row = $result->fetch_assoc()) {
                $zones[] = $row;
            }

            echo json_encode([
                "status" => true,
                "count" => count($zones),
                "data" => $zones
            ], JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
        } else {
            echo json_encode([
                "status" => true,
                "data" => []
            ]);
        }
    }

public function zonesCheck()
{
    header('Content-Type: application/json');

    // Step 1: Get input
    $lat = $_GET['lat'] ?? null;
    $lng = $_GET['lng'] ?? null;
    $zone_id = $_GET['zone_id'] ?? null;

    if (!$lat || !$lng || !$zone_id) {
        http_response_code(400);
        echo json_encode(["status" => false, "message" => "lat, lng, and zone_id are required"]);
        return;
    }

    // Step 2: Fetch coordinates from DB
    $stmt = $this->conn->prepare("SELECT coordinates FROM zones WHERE id = ?");
    $stmt->bind_param("i", $zone_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if (!$result || empty($result['coordinates'])) {
        echo json_encode(["status" => false, "message" => "Zone not found"]);
        return;
    }

    // Step 3: Convert BLOB or string to proper JSON
    $coordinatesBlob = $result['coordinates'];
    $coordinatesJson = is_resource($coordinatesBlob)
        ? stream_get_contents($coordinatesBlob)
        : $coordinatesBlob;

    // Step 4: Double decode if JSON is stringified
    $firstDecode = json_decode($coordinatesJson, true);
    $polygon = is_array($firstDecode) && isset($firstDecode[0]['lat'])
        ? $firstDecode
        : json_decode($firstDecode, true);

    // Step 5: Validate polygon
    if (!$polygon || !is_array($polygon)) {
        echo json_encode(["status" => false, "message" => "Invalid polygon data"]);
        return;
    }

    // Step 6: Check if point is inside polygon
    $isInside = $this->pointInPolygon($lat, $lng, $polygon);

    echo json_encode([
        "status" => true,
        "inside" => $isInside
    ], JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
}




    // Ray-casting point-in-polygon
  private function pointInPolygon($lat, $lng, $polygon)
{
    $inside = false;
    $j = count($polygon) - 1;

    for ($i = 0; $i < count($polygon); $i++) {
        $xi = $polygon[$i]['lat'];
        $yi = $polygon[$i]['lng'];
        $xj = $polygon[$j]['lat'];
        $yj = $polygon[$j]['lng'];

        $intersect = (($yi > $lng) != ($yj > $lng)) &&
                     ($lat < ($xj - $xi) * ($lng - $yi) / ($yj - $yi + 0.0000001) + $xi);
        if ($intersect) {
            $inside = !$inside;
        }
        $j = $i;
    }

    return $inside;
}

}
