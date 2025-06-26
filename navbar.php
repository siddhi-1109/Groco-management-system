<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'db.php';
$cart_count = 0;
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'user') {
    $uid = $_SESSION['user_id'];
    $cart_count = $conn->query("SELECT SUM(quantity) FROM cart_items WHERE user_id = $uid")->fetch_row()[0] ?? 0;
}
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="#">Grocery Management</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav align-items-center">
        <li>
          <form class="d-flex" role="search">
            <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search"/>
            <button class="btn btn-outline-success" type="submit">Search</button>
          </form>
        </li>
      </ul>
    </div>
  </div>
</nav>