<?php
require 'db.php';
session_start();

$fid = $_POST['fid'];
$conn->query("UPDATE feedback SET visible_to_tech = 0 WHERE id = $fid");
header("Location: feedback-summary.php");
