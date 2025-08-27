<?php
function generateUniqueTestimonialId($conn) {
    do {
        $id = substr(strtoupper(bin2hex(random_bytes(3))), 0, 5); // 5-char ID
        $check = $conn->prepare("SELECT COUNT(*) as count FROM testimonials WHERE id=?");
        $check->bind_param("s", $id);
        $check->execute();
        $count = $check->get_result()->fetch_assoc()['count'];
    } while ($count > 0);

    return $id;
}
?>
<?php
function generateUniqueEventId($conn) {
    do {
        $eventId = strtoupper(substr(preg_replace("/[^A-Z0-9]/", "", base64_encode(random_bytes(3))), 0, 5));
        $check = $conn->query("SELECT COUNT(*) AS count FROM events WHERE id='$eventId'");
        $row = $check->fetch_assoc();
    } while ($row['count'] > 0);
    return $eventId;
}

?>
