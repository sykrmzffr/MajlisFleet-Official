<?php
function log_event($dbconn, $event_type, $user_id, $description, $object_type, $object_id) {
    $event_datetime = date('Y-m-d H:i:s');
    $log_query = "INSERT INTO system_logs (event_datetime, event_type, user_id, description, object_type, object_id) VALUES (?, ?, ?, ?, ?, ?)";
    $log_stmt = $dbconn->prepare($log_query);
    $log_stmt->bind_param("ssssss", $event_datetime, $event_type, $user_id, $description, $object_type, $object_id);
    $log_stmt->execute();
    $log_stmt->close();
}
?>