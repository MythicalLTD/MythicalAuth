<?php
include(__DIR__ . '/requirements/page.php');
$ConnectionsPerPage = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $ConnectionsPerPage;

$searchKeyword = isset($_GET['search']) ? $_GET['search'] : '';
$searchCondition = '';
if (!empty($searchKeyword)) {
    $searchCondition = " WHERE `sitename` LIKE '%$searchKeyword%' OR `siteuuid` LIKE '%$searchKeyword%'";
}
$connections_query = "SELECT * FROM connections" . $searchCondition . " ORDER BY `id` LIMIT $offset, $ConnectionsPerPage";
$result = $conn->query($connections_query);
$TotalConnectionsQuery = "SELECT COUNT(*) AS total_connections FROM connections" . $searchCondition;
$totalResult = $conn->query($TotalConnectionsQuery);
$TotalConnections = $totalResult->fetch_assoc()['total_connections'];
$totalPages = ceil($TotalConnections / $ConnectionsPerPage);
?>

<!DOCTYPE html>
<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-semi-dark"
    data-assets-path="<?= $appURL ?>/assets/" data-template="vertical-menu-template">

<head>
    <?php include(__DIR__ . '/requirements/head.php'); ?>
    <title>
        <?= $_CONFIG['app_name'] ?> | Connections
    </title>
    <style>
        .avatar-image {
            width: 30px;
            /* Adjust the size as needed */
            height: 30px;
            /* Adjust the size as needed */
            border-radius: 50%;
        }
    </style>
</head>

<body>
   <!--<div id="preloader" class="discord-preloader">
        <div class="spinner"></div>
    </div>-->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include(__DIR__ . '/components/sidebar.php') ?>
            <div class="layout-page">
                <?php include(__DIR__ . '/components/navbar.php') ?>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">User /</span> Connections</h4>
                        <?php include(__DIR__ . '/components/alert.php') ?>
                        <!-- Search Form -->
                        <form class="mt-4">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" placeholder="Search for connections..." name="search"
                                    value="<?= $searchKeyword ?>">
                                <button class="btn btn-outline-secondary" type="submit">Search</button>
                            </div>
                        </form>
                        <div class="card">
                            <h5 class="card-header">
                                Connections
                            </h5>
                            <div class="table-responsive text-nowrap">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Website name</th>
                                            <th>Website UWID</th>
                                            <th>Connected at</th>
                                            <!--<th>Action</th>-->
                                        </tr>
                                    </thead>
                                    <tbody class="table-border-bottom-0">
                                        <?php
                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>" . $row['sitename'] . "</td>";
                                                echo "<td>" . $row['uwid'] . "</td>";
                                                echo "<td>" . $row['date'] . "</td>";
                                                echo "<!--<td><a href=\"/user/profile?id=" . $row['id'] . "\" class=\"btn btn-primary\">Show</a></td>-->";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo '<td class="text-center" colspan="5"><br>No connections found.<br><br>&nbsp;</td>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center mt-4">
                                <?php
                                for ($i = 1; $i <= $totalPages; $i++) {
                                    echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '"><a class="page-link" href="?page=' . $i . '&search=' . $searchKeyword . '">' . $i . '</a></li>';
                                }
                                ?>
                            </ul>
                        </nav>
                    </div>
                    <?php include(__DIR__ . '/components/footer.php') ?>
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
        <div class="drag-target"></div>
    </div>
    <?php include(__DIR__ . '/requirements/footer.php') ?>
    <script src="<?= $appURL ?>/assets/js/app-user-list.js"></script>
</body>

</html>