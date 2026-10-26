<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tangkap input search
    if (isset($_POST['search'])) {
        $search = trim($_POST['search']);
        $_SESSION['search'] = $search;
        $_SESSION['page'] = 1;
    } else {
        $search = isset($_SESSION['search']) ? $_SESSION['search'] : '';
    }

    // Tangkap input filter predikat (Pastikan bagian ini ada!)
    if (isset($_POST['predikat'])) {
        $predikat_filter = trim($_POST['predikat']);
        $_SESSION['predikat'] = $predikat_filter;
        $_SESSION['page'] = 1;
    } else {
        $predikat_filter = isset($_SESSION['predikat']) ? $_SESSION['predikat'] : 'all';
    }

    if (isset($_POST['page'])) {
        $_SESSION['page'] = (int)$_POST['page'];
    }
} else {
    // Tangkap sorting dari GET
    if (isset($_GET['sort'])) {
        $_SESSION['sort'] = trim($_GET['sort']);
        $_SESSION['page'] = 1;
        header("Location: users.php"); 
        exit();
    }

    $search = isset($_SESSION['search']) ? $_SESSION['search'] : '';
    $predikat_filter = isset($_SESSION['predikat']) ? $_SESSION['predikat'] : 'all';
    $sort_order = isset($_SESSION['sort']) ? $_SESSION['sort'] : '';
}

// Reset filter
if (isset($_POST['reset'])) {
    $search = '';
    $predikat_filter = 'all';
    $sort_order = '';
    $_SESSION['page'] = 1;
    unset($_SESSION['search']);
    unset($_SESSION['predikat']);
    unset($_SESSION['sort']);
    unset($_SESSION['page']);
}

$limit = 8; // Sesuai permintaan 7-8 data per halaman
$page = isset($_SESSION['page']) ? (int)$_SESSION['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Susun Kondisi WHERE
$where_clauses = [];
if ($search !== '') {
    $escaped_search = mysqli_real_escape_string($conn, $search);
    $where_clauses[] = "nama_mhsw LIKE '%$escaped_search%'";
}
if ($predikat_filter !== 'all' && $predikat_filter !== '') {
    $escaped_predikat = mysqli_real_escape_string($conn, $predikat_filter);
    $where_clauses[] = "predikat = '$escaped_predikat'";
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}


// Susun Pengurutan (Sorting Nilai Akhir dengan Default)
$sort_order = isset($_SESSION['sort']) ? $_SESSION['sort'] : '';
$order_sql = "";

if ($sort_order === 'ASC') {
    $order_sql = "ORDER BY nilai_akhir ASC";
} elseif ($sort_order === 'DESC') {
    $order_sql = "ORDER BY nilai_akhir DESC";
} else {
    $order_sql = ""; // Default: Sesuai urutan input database
}

// Hitung Total Data untuk Paging
$count_sql = "SELECT COUNT(*) AS total FROM data_mhsw $where_sql";
$count_result = mysqli_query($conn, $count_sql);
$total_data = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_data / $limit);

if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
    $_SESSION['page'] = $page;
    $offset = ($page - 1) * $limit;
}

$sql = "SELECT * FROM data_mhsw $where_sql $order_sql LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);
?>

<!doctype html>
<html lang="en">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Rekap Data Nilai Mahasiswa</title>

    <!--begin::Theme Init (prevents flash of incorrect theme on load, #6043)-->
    <script>
      (() => {
        'use strict';
        const root = document.documentElement;

        // Applications with their own theming opt out of AdminLTE's color mode
        // entirely, here as well as in the bundle.
        if (root.getAttribute('data-lte-color-mode') === 'off') {
          return;
        }

        const STORAGE_KEY = 'lte-theme';
        let stored = null;
        try {
          stored = localStorage.getItem(STORAGE_KEY);
        } catch {
          // localStorage may be unavailable (private mode, sandboxed iframe).
        }
        // Mirror the precedence in color-mode.ts: the visitor's stored choice
        // wins, then a theme this page declared itself, then the OS preference.
        const authored = root.getAttribute('data-bs-theme');
        let resolved = 'light';
        if (stored === 'dark' || stored === 'light') {
          resolved = stored;
        } else if (authored === 'dark' || authored === 'light') {
          resolved = authored;
        } else if (globalThis.matchMedia('(prefers-color-scheme: dark)').matches) {
          resolved = 'dark';
        }
        root.setAttribute('data-bs-theme', resolved);
        root.style.colorScheme = resolved;
        // Flag values computed here, so the bundle does not mistake them for a
        // theme the page declared and stop following the OS preference.
        if (resolved !== authored) {
          root.setAttribute('data-lte-theme-resolved', '');
        }
      })();
    </script>
    <!--end::Theme Init-->

    <!--begin::Accessibility Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
    <!--end::Accessibility Meta Tags-->

    <!--begin::Primary Meta Tags-->
    <meta name="title" content="AdminLTE 4 | Users" />
    <meta name="author" content="ColorlibHQ" />
    <meta
      name="description"
      content="AdminLTE is a free Bootstrap 5 admin dashboard template with almost 50 example pages, built with vanilla JS and designed with accessibility in mind."
    />
    <meta
      name="keywords"
      content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard, accessible admin panel"
    />
    <!--end::Primary Meta Tags-->

    <!--begin::Accessibility Features-->
    <!-- Skip links will be dynamically added by accessibility.js -->
    <meta name="supported-color-schemes" content="light dark" />
    <link rel="preload" href="./css/adminlte.css" as="style" />
    <!--end::Accessibility Features-->

    <!--begin::Fonts-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
      integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
      crossorigin="anonymous"
      media="print"
      onload="this.media = 'all'"
    />
    <!--end::Fonts-->

    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->

    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->

    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="./css/adminlte.css" />
    <!--end::Required Plugin(AdminLTE)-->
  </head>
  <!--end::Head-->
  <!--begin::Body-->
  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
      <!--begin::Header-->
      <nav class="app-header navbar navbar-expand bg-body">
        <!--begin::Container-->
        <div class="container-fluid">
          <!--begin::Start Navbar Links-->
          <ul class="navbar-nav">
            <li class="nav-item">
              <a
                class="nav-link"
                data-lte-toggle="sidebar"
                href="#"
                role="button"
                aria-label="Toggle sidebar"
              >
                <i class="bi bi-list"></i>
              </a>
            </li>

            <li class="nav-item d-none d-md-block">
              <a href="./index.html" class="nav-link">
                <i class="bi bi-grid-1x2 me-1" aria-hidden="true"></i>
                Live preview
              </a>
            </li>
            <li class="nav-item d-none d-md-block">
              <a href="./docs/introduction.html" class="nav-link">
                <i class="bi bi-book me-1" aria-hidden="true"></i>
                Documentation
              </a>
            </li>
          </ul>
          <!--end::Start Navbar Links-->

          <!--begin::Navbar Search-->
          <form
            class="navbar-search d-none d-md-block ms-3"
            role="search"
            action="./pages/search-results.html"
          >
            <label for="navbar-search-input" class="visually-hidden">Search</label>
            <div class="navbar-search-field">
              <input
                type="search"
                id="navbar-search-input"
                name="q"
                class="form-control"
                placeholder="Search…"
                autocomplete="off"
              />
              <button class="navbar-search-submit" type="submit" aria-label="Submit search">
                <i class="bi bi-search" aria-hidden="true"></i>
              </button>
            </div>
          </form>
          <!--end::Navbar Search-->

          <!--begin::End Navbar Links-->
          <ul class="navbar-nav ms-auto">
            <!--begin::Search (small screens: the field above is hidden, so link to the search page)-->
            <li class="nav-item d-md-none">
              <a class="nav-link" href="./pages/search-results.html" aria-label="Search">
                <i class="bi bi-search" aria-hidden="true"></i>
              </a>
            </li>
            <!--end::Search-->
            
            <!--begin::Messages Dropdown Menu-->
            <li class="nav-item dropdown">
              <a
                class="nav-link"
                data-bs-toggle="dropdown"
                href="#"
                aria-label="Messages: 3 unread"
              >
                <i class="bi bi-chat-text"></i>
                <span class="navbar-badge badge text-bg-danger">3</span>
              </a>
              <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <a href="#" class="dropdown-item">
                  <!--begin::Message-->
                  <div class="d-flex">
                    <div class="flex-shrink-0">
                      <img
                        src="./assets/img/user1-128x128.jpg"
                        alt=""
                        class="img-size-50 rounded-circle me-3"
                      />
                    </div>
                    <div class="flex-grow-1">
                      <p class="dropdown-item-title">
                        Brad Diesel
                        <span class="float-end fs-7 text-danger"
                          ><i class="bi bi-star-fill"></i
                        ></span>
                      </p>
                      <p class="fs-7">Call me whenever you can...</p>
                      <p class="fs-7 text-secondary">
                        <i class="bi bi-clock-fill me-1"></i> 4 Hours Ago
                      </p>
                    </div>
                  </div>
                  <!--end::Message-->
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                  <!--begin::Message-->
                  <div class="d-flex">
                    <div class="flex-shrink-0">
                      <img
                        src="./assets/img/user8-128x128.jpg"
                        alt=""
                        class="img-size-50 rounded-circle me-3"
                      />
                    </div>
                    <div class="flex-grow-1">
                      <p class="dropdown-item-title">
                        John Pierce
                        <span class="float-end fs-7 text-secondary">
                          <i class="bi bi-star-fill"></i>
                        </span>
                      </p>
                      <p class="fs-7">I got your message bro</p>
                      <p class="fs-7 text-secondary">
                        <i class="bi bi-clock-fill me-1"></i> 4 Hours Ago
                      </p>
                    </div>
                  </div>
                  <!--end::Message-->
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                  <!--begin::Message-->
                  <div class="d-flex">
                    <div class="flex-shrink-0">
                      <img
                        src="./assets/img/user3-128x128.jpg"
                        alt=""
                        class="img-size-50 rounded-circle me-3"
                      />
                    </div>
                    <div class="flex-grow-1">
                      <p class="dropdown-item-title">
                        Nora Silvester
                        <span class="float-end fs-7 text-warning">
                          <i class="bi bi-star-fill"></i>
                        </span>
                      </p>
                      <p class="fs-7">The subject goes here</p>
                      <p class="fs-7 text-secondary">
                        <i class="bi bi-clock-fill me-1"></i> 4 Hours Ago
                      </p>
                    </div>
                  </div>
                  <!--end::Message-->
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
              </div>
            </li>
            <!--end::Messages Dropdown Menu-->

            <!--begin::Notifications Dropdown Menu-->
            <li class="nav-item dropdown">
              <a
                class="nav-link"
                data-bs-toggle="dropdown"
                href="#"
                aria-label="Notifications: 15 unread"
              >
                <i class="bi bi-bell-fill"></i>
                <span class="navbar-badge badge text-bg-warning">15</span>
              </a>
              <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <span class="dropdown-item dropdown-header">15 Notifications</span>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                  <i class="bi bi-envelope me-2"></i> 4 new messages
                  <span class="float-end text-secondary fs-7">3 mins</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                  <i class="bi bi-people-fill me-2"></i> 8 friend requests
                  <span class="float-end text-secondary fs-7">12 hours</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                  <i class="bi bi-file-earmark-fill me-2"></i> 3 new reports
                  <span class="float-end text-secondary fs-7">2 days</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item dropdown-footer"> See All Notifications </a>
              </div>
            </li>
            <!--end::Notifications Dropdown Menu-->

            <!--begin::Language Menu-->
            <li class="nav-item dropdown">
              <a
                class="nav-link"
                href="#"
                id="language-menu"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                aria-label="Change language, current language English"
              >
                <i class="bi bi-translate" aria-hidden="true"></i>
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="language-menu">
                <li>
                  <a class="dropdown-item active" href="#" hreflang="en" aria-current="true">
                    English
                    <i class="bi bi-check-lg ms-2" aria-hidden="true"></i>
                  </a>
                </li>
                <li><a class="dropdown-item" href="#" hreflang="es">Español</a></li>
                <li><a class="dropdown-item" href="#" hreflang="fr">Français</a></li>
                <li><a class="dropdown-item" href="#" hreflang="de">Deutsch</a></li>
                <li><a class="dropdown-item" href="#" hreflang="ar">العربية</a></li>
              </ul>
            </li>
            <!--end::Language Menu-->

            <!--begin::Fullscreen Toggle-->
            <li class="nav-item">
              <a
                class="nav-link"
                href="#"
                data-lte-toggle="fullscreen"
                aria-label="Toggle fullscreen"
              >
                <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                <i data-lte-icon="minimize" class="bi bi-fullscreen-exit d-none"></i>
              </a>
            </li>
            <!--end::Fullscreen Toggle-->

            <!--begin::Color Mode Toggle-->
            <li class="nav-item dropdown">
              <a
                class="nav-link"
                href="#"
                id="bd-theme"
                aria-label="Toggle color scheme"
                data-bs-toggle="dropdown"
                aria-expanded="false"
              >
                <i class="bi bi-sun-fill" data-lte-theme-icon="light"></i>
                <i class="bi bi-moon-fill d-none" data-lte-theme-icon="dark"></i>
                <i class="bi bi-circle-half d-none" data-lte-theme-icon="auto"></i>
              </a>
              <ul
                class="dropdown-menu dropdown-menu-end"
                aria-labelledby="bd-theme"
                style="--bs-dropdown-min-width: 8rem"
              >
                <li>
                  <button
                    type="button"
                    class="dropdown-item d-flex align-items-center"
                    data-bs-theme-value="light"
                    aria-pressed="false"
                  >
                    <i class="bi bi-sun-fill me-2"></i>
                    Light
                    <i class="bi bi-check-lg ms-auto d-none"></i>
                  </button>
                </li>
                <li>
                  <button
                    type="button"
                    class="dropdown-item d-flex align-items-center"
                    data-bs-theme-value="dark"
                    aria-pressed="false"
                  >
                    <i class="bi bi-moon-fill me-2"></i>
                    Dark
                    <i class="bi bi-check-lg ms-auto d-none"></i>
                  </button>
                </li>
                <li>
                  <button
                    type="button"
                    class="dropdown-item d-flex align-items-center active"
                    data-bs-theme-value="auto"
                    aria-pressed="true"
                  >
                    <i class="bi bi-circle-half me-2"></i>
                    Auto
                    <i class="bi bi-check-lg ms-auto d-none"></i>
                  </button>
                </li>
              </ul>
            </li>
            <!--end::Color Mode Toggle-->

            <!--begin::User Menu Dropdown-->
            <li class="nav-item dropdown user-menu">
              <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                <img
                  src="./assets/img/user2-160x160.jpg"
                  class="user-image rounded-circle shadow"
                  alt="Alexander Pierce"
                />
                <span class="d-none d-md-inline">Alexander Pierce</span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <!--begin::User Image-->
                <li class="user-header text-bg-primary">
                  <img
                    src="./assets/img/user2-160x160.jpg"
                    class="rounded-circle shadow"
                    alt="Alexander Pierce"
                  />
                  <p>
                    Alexander Pierce - Web Developer
                    <small>Member since Nov. 2023</small>
                  </p>
                </li>
                <!--end::User Image-->
                <!--begin::Menu Body-->
                <li class="user-body">
                  <!--begin::Row-->
                  <div class="row">
                    <div class="col-4 text-center">
                      <a href="#">Followers</a>
                    </div>
                    <div class="col-4 text-center">
                      <a href="#">Sales</a>
                    </div>
                    <div class="col-4 text-center">
                      <a href="#">Friends</a>
                    </div>
                  </div>
                  <!--end::Row-->
                </li>
                <!--end::Menu Body-->
                <!--begin::Menu Footer-->
                <li class="user-footer">
                  <a href="#" class="btn btn-outline-secondary">Profile</a>
                  <a href="#" class="btn btn-outline-danger float-end">Sign out</a>
                </li>
                <!--end::Menu Footer-->
              </ul>
            </li>
            <!--end::User Menu Dropdown-->
          </ul>
          <!--end::End Navbar Links-->
        </div>
        <!--end::Container-->
      </nav>
      <!--end::Header-->

      <!--begin::Sidebar-->
      <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <!--begin::Sidebar Brand-->
        <div class="sidebar-brand">
          <!--begin::Brand Link-->
          <a href="./index.html" class="brand-link">
            <!--begin::Brand Image-->
            <img
              src="./assets/img/AdminLTELogo.png"
              alt="AdminLTE Logo"
              class="brand-image opacity-75 shadow"
            />
            <!--end::Brand Image-->
            <!--begin::Brand Text-->
            <span class="brand-text fw-light">AdminLTE 4</span>
            <!--end::Brand Text-->
          </a>
          <!--end::Brand Link-->
        </div>
        <!--end::Sidebar Brand-->

        <!--begin::Sidebar Search-->
        <div class="sidebar-search" role="search">
          <label for="sidebar-search-input" class="visually-hidden">Filter menu</label>
          <input
            type="search"
            id="sidebar-search-input"
            class="form-control form-control-sm"
            placeholder="Filter menu…"
            autocomplete="off"
            data-lte-toggle="sidebar-search"
            data-lte-target="#navigation"
          />
          <p class="fs-7 text-secondary mt-2 mb-0" data-lte-search-empty role="status" hidden>
            No matching pages.
          </p>
        </div>
        <!--end::Sidebar Search-->

        <!--begin::Sidebar Wrapper-->
        <div class="sidebar-wrapper">
          <nav class="mt-2" aria-label="Main navigation">
            <!--begin::Sidebar Menu-->
            <ul
              class="nav sidebar-menu flex-column"
              data-lte-toggle="treeview"
              data-accordion="false"
              id="navigation"
            >
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-speedometer"></i>
                  <p>
                    Dashboard
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="./index.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Dashboard v1</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./index2.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Dashboard v2</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./index3.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Dashboard v3</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item">
                <a href="./starter.html" class="nav-link">
                  <i class="nav-icon bi bi-file-earmark"></i>
                  <p>Starter Page</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="./generate/theme.html" class="nav-link">
                  <i class="nav-icon bi bi-palette"></i>
                  <p>Theme Generate</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-box-seam-fill"></i>
                  <p>
                    Widgets
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="./widgets/small-box.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Small Box</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./widgets/info-box.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>info Box</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./widgets/cards.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Cards</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./widgets/social.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Social &amp; Post</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-clipboard-fill"></i>
                  <p>
                    Layout Options
                    <span class="nav-badge badge text-bg-secondary me-3">12</span>
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="./layout/unfixed-sidebar.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Default Sidebar</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./layout/fixed-sidebar.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Fixed Sidebar</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./layout/fixed-header.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Fixed Header</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./layout/fixed-footer.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Fixed Footer</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./layout/fixed-complete.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Fixed Complete</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./layout/layout-custom-area.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Layout <small>+ Custom Area </small></p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./layout/sidebar-mini.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Sidebar Mini</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./layout/collapsed-sidebar.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Sidebar Mini <small>+ Collapsed</small></p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./layout/collapsed-sidebar-without-hover.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Sidebar Mini <small>+ Collapsed + No Hover</small></p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./layout/logo-switch.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Sidebar Mini <small>+ Logo Switch</small></p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./layout/top-nav.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Top Nav <small>+ No Sidebar</small></p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./layout/layout-rtl.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Layout RTL</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-tree-fill"></i>
                  <p>
                    UI Elements
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="./UI/general.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>General</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./UI/icons.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Icons</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./UI/timeline.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Timeline</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./UI/ribbons.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Ribbons</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./UI/colors.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Colors</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-envelope"></i>
                  <p>
                    Mailbox
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="./mailbox/inbox.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Inbox</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./mailbox/read.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Read Message</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./mailbox/compose.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Compose</p>
                    </a>
                  </li>
                </ul>
              </li>

              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-pencil-square"></i>
                  <p>
                    Forms
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="./forms/elements.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Elements</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./forms/layout.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Layout</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./forms/validation.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Validation</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./forms/wizard.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Wizard</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./forms/advanced.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Advanced Elements</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./forms/editors.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Editors</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-table"></i>
                  <p>
                    Tables
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="./tables/simple.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Simple Tables</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./tables/data.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Data Tables</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-graph-up"></i>
                  <p>
                    Charts
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="./charts/chartjs.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Chart.js</p>
                    </a>
                  </li>
                </ul>
              </li>

              <li class="nav-header">PAGES</li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-file-earmark-text"></i>
                  <p>
                    Pages
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="./pages/profile.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Profile</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./pages/settings.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Settings</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./pages/invoice.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Invoice</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./pages/calendar.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Calendar</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./pages/kanban.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Kanban</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./pages/chat.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Chat</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./pages/file-manager.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>File Manager</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./pages/projects.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Projects</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./pages/gallery.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Gallery</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./pages/search-results.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Search Results</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./pages/pricing.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Pricing</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="./pages/faq.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>FAQ</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>
                        Error
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview">
                      <li class="nav-item">
                        <a href="./pages/404.html" class="nav-link">
                          <i class="nav-icon bi bi-circle"></i>
                          <p>404</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="./pages/500.html" class="nav-link">
                          <i class="nav-icon bi bi-circle"></i>
                          <p>500</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="./pages/maintenance.html" class="nav-link">
                          <i class="nav-icon bi bi-circle"></i>
                          <p>Maintenance</p>
                        </a>
                      </li>
                    </ul>
                  </li>
                </ul>
              </li>
              <li class="nav-item">
                <a href="./users.html" class="nav-link active">
                  <i class="nav-icon bi bi-people"></i>
                  <p>Nilai Mahasiswa</p>
                </a>
              </li>

              <li class="nav-header">EXAMPLES</li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-box-arrow-in-right"></i>
                  <p>
                    Auth
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="nav-icon bi bi-box-arrow-in-right"></i>
                      <p>
                        Version 1
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview">
                      <li class="nav-item">
                        <a href="./examples/login.html" class="nav-link">
                          <i class="nav-icon bi bi-circle"></i>
                          <p>Login</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="./examples/register.html" class="nav-link">
                          <i class="nav-icon bi bi-circle"></i>
                          <p>Register</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="./examples/forgot-password.html" class="nav-link">
                          <i class="nav-icon bi bi-circle"></i>
                          <p>Forgot Password</p>
                        </a>
                      </li>
                    </ul>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="nav-icon bi bi-box-arrow-in-right"></i>
                      <p>
                        Version 2
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview">
                      <li class="nav-item">
                        <a href="./examples/login-v2.html" class="nav-link">
                          <i class="nav-icon bi bi-circle"></i>
                          <p>Login</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="./examples/register-v2.html" class="nav-link">
                          <i class="nav-icon bi bi-circle"></i>
                          <p>Register</p>
                        </a>
                      </li>
                    </ul>
                  </li>
                  <li class="nav-item">
                    <a href="./examples/lockscreen.html" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Lockscreen</p>
                    </a>
                  </li>
                </ul>
              </li>

              <li class="nav-header">MULTI LEVEL EXAMPLE</li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-circle-fill"></i>
                  <p>Level 1</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-circle-fill"></i>
                  <p>
                    Level 1
                    <i class="nav-arrow bi bi-chevron-right"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Level 2</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>
                        Level 2
                        <i class="nav-arrow bi bi-chevron-right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview">
                      <li class="nav-item">
                        <a href="#" class="nav-link">
                          <i class="nav-icon bi bi-record-circle-fill"></i>
                          <p>Level 3</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="#" class="nav-link">
                          <i class="nav-icon bi bi-record-circle-fill"></i>
                          <p>Level 3</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a href="#" class="nav-link">
                          <i class="nav-icon bi bi-record-circle-fill"></i>
                          <p>Level 3</p>
                        </a>
                      </li>
                    </ul>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="nav-icon bi bi-circle"></i>
                      <p>Level 2</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-circle-fill"></i>
                  <p>Level 1</p>
                </a>
              </li>

              <li class="nav-header">LABELS</li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-circle text-danger"></i>
                  <p class="text">Important</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-circle text-warning"></i>
                  <p>Warning</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon bi bi-circle text-info"></i>
                  <p>Informational</p>
                </a>
              </li>
            </ul>
            <!--end::Sidebar Menu-->

            <!-- Docs CTA (bottom of sidebar) -->
            <div class="p-3 mt-3 border-top border-secondary border-opacity-25">
              <a
                href="./docs/introduction.html"
                class="btn btn-sm btn-outline-light w-100 d-flex align-items-center justify-content-center gap-2"
              >
                <i class="bi bi-book" aria-hidden="true"></i>
                View documentation
              </a>
            </div>
          </nav>
        </div>
        <!--end::Sidebar Wrapper-->
      </aside>
      <!--end::Sidebar-->

      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Data Mahasiswa</h1>
              </div>
              <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Nilai Mahasiswa</li>
                  </ol>
                </nav>
              </div>
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content Header-->

        <!--begin::App Content-->
        <div class="app-content">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-12">

                <!--begin::Card-->

                <!--begin::Card-->
                <div class="card mb-4">
                  <!--begin::Card Header-->
                  <div class="card-header">
                    <div class="row g-2 align-items-center">
                      <div class="col-12 col-md-4">
                        <h3 class="card-title">User Directory</h3>
                      </div>
                      <div class="col-12 col-md-8">
                        <!-- Form Searching, Filter Predikat, & Sorting menggunakan POST -->
                        <form method="POST" action="" class="d-flex flex-wrap justify-content-md-end gap-2">
                          <div class="input-group input-group-sm w-auto">
                            <span class="input-group-text">
                              <i class="bi bi-search" aria-hidden="true"></i>
                            </span>
                            <input
                              type="search"
                              name="search"
                              id="user-search"
                              class="form-control"
                              placeholder="Cari nama mahasiswa"
                              aria-label="Search users"
                              value="<?php echo htmlspecialchars($search); ?>"
                              style="width: 150px"
                            />
                          </div>
                          
                          <!-- Filter Predikat Sesuai Standar Asli -->
                          <!-- Filter Predikat dengan onchange agar otomatis memfilter saat dipilih -->
                          <select
                            name="predikat"
                            id="user-role-filter"
                            class="form-select form-select-sm w-auto"
                            aria-label="Filter by predikat"
                            onchange="this.form.submit()"
                          >
                            <option value="all" <?php if($predikat_filter == 'all') echo 'selected'; ?>>Semua Predikat</option>
                            <option value="Sangat Memuaskan" <?php if($predikat_filter == 'Sangat Memuaskan') echo 'selected'; ?>>Sangat Memuaskan</option>
                            <option value="Memuaskan" <?php if($predikat_filter == 'Memuaskan') echo 'selected'; ?>>Memuaskan</option>
                            <option value="Cukup" <?php if($predikat_filter == 'Cukup') echo 'selected'; ?>>Cukup</option>
                            <option value="Kurang" <?php if($predikat_filter == 'Kurang') echo 'selected'; ?>>Kurang</option>
                            <option value="Tidak Lulus" <?php if($predikat_filter == 'Tidak Lulus') echo 'selected'; ?>>Tidak Lulus</option>
                          </select>

                          <button
                            type="button"
                            class="btn btn-sm btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#modal-add-user"
                          >
                            <i class="bi bi-person-plus-fill me-1" aria-hidden="true"></i>
                            Tambah Data Nilai
                          </button>
                        </form>
                      </div>
                    </div>
                  </div>
                  <!--end::Card Header-->

                  <!--begin::Card Body-->
                  <div class="card-body p-0">
                    <?php if (mysqli_num_rows($result) > 0) { ?>
                    <div class="table-responsive">
                      <table class="table table-hover align-middle m-0">
                        <thead>
                          <tr>
                            <th>Nim</th>
                            <th>Nama Mahasiswa</th>
                            <th>
                              <?php 
                                // Tentukan arah sorting berikutnya saat diklik: 
                                // Jika kosong/default -> ASC -> DESC -> kembali kosong (default)
                                if ($sort_order == '') {
                                    $next_sort = 'ASC';
                                    $title_text = 'Urutkan Terkecil (ASC)';
                                } elseif ($sort_order == 'ASC') {
                                    $next_sort = 'DESC';
                                    $title_text = 'Urutkan Terbesar (DESC)';
                                } else {
                                    $next_sort = '';
                                    $title_text = 'Kembalikan ke Urutan Default';
                                }
                              ?>
                              <a href="?sort=<?php echo $next_sort; ?>" class="text-dark text-decoration-none d-flex align-items-center justify-content-between" title="<?php echo $title_text; ?>">
                                Nilai Akhir
                                <!-- Ikon Panah Kecil dengan Indikator Status -->
                                <span class="ms-1">
                                  <?php if ($sort_order == 'ASC'): ?>
                                    <i class="bi bi-arrow-up text-primary" style="font-size: 0.85rem;" title="Aktif: Ascending"></i>
                                  <?php elseif ($sort_order == 'DESC'): ?>
                                    <i class="bi bi-arrow-down text-primary" style="font-size: 0.85rem;" title="Aktif: Descending"></i>
                                  <?php else: ?>
                                    <i class="bi bi-arrow-down-up text-muted" style="font-size: 0.75rem;" title="Default (Klik untuk ubah)"></i>
                                  <?php endif; ?>
                                </span>
                              </a>
                            </th>
                            <th>Nilai Huruf</th>
                            <th>Predikat</th>
                            <th>Tanggal Dibuat</th>
                            <th>Tanggal Diubah</th>
                            <th class="text-end">Actions</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php while($row = mysqli_fetch_assoc($result)) { 
                            // Cek ketersediaan file foto di folder uploads, jika tidak ada pakai default AdminLTE
                            $foto_path = (!empty($row["foto"]) && file_exists(__DIR__ . "/uploads/" . $row["foto"]))
                                         ? "./uploads/" . htmlspecialchars($row["foto"])
                                         : "./assets/img/user1-128x128.jpg";
                          ?>
                          <tr>
                            <!-- Kolom Nim -->
                            <td>
                              <span class="fw-medium"><?php echo htmlspecialchars($row["nim"]); ?></span>
                            </td>

                            <!-- Kolom Nama Mahasiswa -->
                            <td> 
                              <div class="d-flex align-items-center">
                                <img
                                  src="<?php echo $foto_path; ?>"
                                  alt="Foto Mahasiswa"
                                  class="img-size-32 rounded-circle me-2"
                                  style="width: 32px; height: 32px; object-fit: cover;"
                                />
                                <span><?php echo htmlspecialchars($row["nama_mhsw"]); ?></span>
                              </div>
                            </td>

                            <!-- Kolom Nilai Akhir -->
                            <td>
                              <span class="badge text-bg-danger"> 
                                <?php echo htmlspecialchars($row["nilai_akhir"]); ?> 
                              </span>
                            </td>

                            <!-- Kolom Nilai Huruf -->
                            <td>
                              <span class="badge text-bg-success">
                                <?php echo htmlspecialchars($row["nilai_hrf"]); ?>
                              </span>
                            </td>

                            <!-- Kolom Predikat -->
                            <td>
                              <?php echo htmlspecialchars($row["predikat"]); ?>
                            </td>

                            <!-- Tampilkan Tanggal Dibuat -->
                            <td>
                              <small class="text-secondary"><?php echo htmlspecialchars($row["created_at"]); ?></small>
                            </td>

                            <!-- Tampilkan Tanggal Diubah -->
                            <td>
                              <small class="text-secondary"><?php echo htmlspecialchars($row["updated_at"]); ?></small>
                            </td>

                            <!-- Kolom Aksi -->
                            <td class="text-end">
                              <div class="btn-group btn-group-sm">
                                <button
                                  type="button"
                                  class="btn btn-outline-secondary"
                                  data-bs-toggle="modal"
                                  data-bs-target="#modal-edit-user"
                                  data-nim="<?php echo htmlspecialchars($row['nim']); ?>"
                                  data-nama="<?php echo htmlspecialchars($row['nama_mhsw']); ?>"
                                  data-nilai="<?php echo htmlspecialchars($row['nilai_akhir']); ?>"
                                  aria-label="Edit data mahasiswa"
                                >
                                  <i class="bi bi-pencil" aria-hidden="true"></i>
                                </button>
                                <button
                                  type="button"
                                  class="btn btn-outline-danger"
                                  data-bs-toggle="modal"
                                  data-bs-target="#modal-delete-user"
                                  data-nim="<?php echo htmlspecialchars($row['nim']); ?>"
                                  aria-label="Hapus Nilai Ini">
                                  <i class="bi bi-trash" aria-hidden="true"></i>
                                </button>
                              </div>
                            </td>
                          </tr>
                          <?php } ?>  
                        </tbody>
                      </table>
                    </div>
                    <?php } else { ?>
                      <div class="p-4 text-center text-secondary">0 results - Data Not Found!</div>
                    <?php } ?>
                    <?php mysqli_close($conn); ?>
                  </div>
                  <!--end::Card Body-->

                  <!--begin::Card Footer-->
                  <div class="card-footer clearfix">
                    <div class="float-start pt-1 fs-7 text-body-secondary">
                      <?php
                      $start_num = ($total_data > 0) ? $offset + 1 : 0;
                      $end_num = min($offset + $limit, $total_data);
                      echo "Showing $start_num to $end_num of $total_data users";
                      ?>
                    </div>

                    <?php if ($total_pages > 1): ?>
                    <form method="POST" action="" class="m-0 float-end">
                      <ul class="pagination pagination-sm m-0">
                        <!-- Tombol Previous -->
                        <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>">
                          <button type="submit" name="page" value="<?php echo $page - 1; ?>" class="page-link" <?php if($page <= 1) echo 'disabled'; ?>> &laquo; </button>
                        </li>

                        <!-- Nomor Halaman Dinamis -->
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                          <li class="page-item <?php if($page == $i) echo 'active'; ?>">
                            <button type="submit" name="page" value="<?php echo $i; ?>" class="page-link">
                              <?php echo $i; ?>
                            </button>
                          </li>
                        <?php endfor; ?>

                        <!-- Tombol Next -->
                        <li class="page-item <?php if($page >= $total_pages) echo 'disabled'; ?>">
                          <button type="submit" name="page" value="<?php echo $page + 1; ?>" class="page-link" <?php if($page >= $total_pages) echo 'disabled'; ?>> &raquo; </button>
                        </li>
                      </ul>
                    </form>
                    <?php endif; ?>
                  </div>
                  <!--end::Card Footer-->
                </div>
                <!--end::Card-->
              </div>
              <!-- /.col -->
            </div>
            <!--end::Row-->

            <!--begin::Add User Modal-->
            <div
              class="modal fade"
              id="modal-add-user"
              tabindex="-1"
              aria-labelledby="modal-add-user-label"
              aria-hidden="true"
            >
              <div class="modal-dialog">
                <div class="modal-content">

                  <!-- Ditambahkan enctype="multipart/form-data" agar form bisa mengunggah file foto -->
                  <form action="insert_nilai.php" method="post" enctype="multipart/form-data">
                    <div class="modal-header">
                      <h5 class="modal-title" id="modal-add-user-label">Tambah Data Nilai</h5>
                      <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                      ></button>
                    </div>
                    <div class="modal-body">
                      <div class="mb-3">
                        <label for="new-user-name" class="form-label"> Nim </label>
                        <input
                          type="text"
                          class="form-control"
                          name="nim"
                          id="new-user-name"
                          placeholder="Maksimal 10 karakter"
                          maxlength="10"
                          required
                        />
                      </div>
                      <div class="mb-3">
                        <label for="new-user-email" class="form-label"> Nama </label>
                        <input
                          type="text"
                          class="form-control"
                          name="name"
                          id="new-user-email"
                          placeholder="Masukkan Nama Anda"
                          required
                        />
                      </div>
                      <div class="mb-3">
                        <label for="new-user-nilai" class="form-label"> Nilai Akhir </label>
                        <input
                          type="number"
                          class="form-control"
                          id="new-user-nilai"
                          placeholder="Masukkan nilai anda" 
                          min="0" 
                          max="100" 
                          step="0.01" 
                          name="nilaia"
                          required
                        />
                      </div>
                      <div class="mb-3">
                        <label for="new-user-foto" class="form-label"> Foto Mahasiswa </label>
                        <input
                          type="file"
                          class="form-control"
                          id="new-user-foto"
                          name="foto"
                          accept="image/*"
                        />
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batal
                      </button>
                      <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <!--end::Add User Modal-->

            <!-- Modal Delete -->
            <div
              class="modal fade"
              id="modal-delete-user"
              tabindex="-1"
              aria-labelledby="modal-delete-user-label"
              aria-hidden="true"
            >
              <div class="modal-dialog">
                <div class="modal-content">
                  <form action="delete_data.php" method="post">
                    <div class="modal-header">
                      <h5 class="modal-title" id="modal-delete-user-label">
                        Hapus Data Mahasiswa
                      </h5>
                      <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                      ></button>
                    </div>
                    <div class="modal-body">
                      <p class="mb-0">
                        Apakah kamu yakin ingin menghapus data mahasiswa ini?
                        Data yang dihapus tidak dapat dikembalikan.
                      </p>
                      <input type="hidden" name="nim" id="delete-nim">
                    </div>
                    <div class="modal-footer">
                      <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                      >
                        Batal
                      </button>
                      <button type="submit" class="btn btn-danger">
                        Hapus Data
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <!--end::Delete User Modal-->
            
            <!-- Modal Edit -->
            <div
              class="modal fade"
              id="modal-edit-user"
              tabindex="-1"
              aria-labelledby="modal-edit-user-label"
              aria-hidden="true"
            >
              <div class="modal-dialog">
                <div class="modal-content">
                  <form action="edit_data.php" method="post" enctype="multipart/form-data">
                    <div class="modal-header">
                      <h5 class="modal-title" id="modal-edit-user-label">
                        Edit Data Mahasiswa
                      </h5>
                      <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                      ></button>
                    </div>
                    <div class="modal-body">
                      <!-- NIM lama disimpan di input hidden untuk acuan WHERE di database -->
                      <input
                        type="hidden"
                        name="nim_lama"
                        id="edit-nim-lama"
                      />

                      <div class="mb-3">
                        <label for="edit-nim" class="form-label">NIM</label>
                        <input
                          type="text"
                          class="form-control"
                          name="nim"
                          id="edit-nim"
                          maxlength="10"
                          required
                        />
                      </div>

                      <div class="mb-3">
                        <label for="edit-nama" class="form-label">
                          Nama Mahasiswa
                        </label>
                        <input
                          type="text"
                          class="form-control"
                          name="name"
                          id="edit-nama"
                          required
                        />
                      </div>

                      <div class="mb-3">
                        <label for="edit-nilai" class="form-label">
                          Nilai Akhir
                        </label>
                        <input
                          type="number"
                          class="form-control"
                          name="nilaia"
                          id="edit-nilai"
                          min="0"
                          max="100"
                          step="0.01"
                          required
                        />
                      </div>

                      <div class="mb-3">
                        <label for="edit-foto" class="form-label">
                          Ganti Foto (Kosongkan jika tidak diubah)
                        </label>
                        <input
                          type="file"
                          class="form-control"
                          name="foto"
                          id="edit-foto"
                          accept="image/*"
                        />
                      </div>
                    </div>

                    <div class="modal-footer">
                      <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                      >
                        Batal
                      </button>
                      <button type="submit" class="btn btn-primary">
                        Simpan Perubahan
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <!-- End Modal Edit -->

          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content-->
      </main>
      <!--end::App Main-->

      <!--begin::Footer-->
      <footer class="app-footer">
        <div class="float-end d-none d-sm-inline">Anything you want</div>
        <strong>
          Copyright &copy; 2014-2026&nbsp;
          <a href="https://adminlte.io" class="text-decoration-none">AdminLTE.io</a>.
        </strong>
        All rights reserved.
      </footer>
      <!--end::Footer-->
    </div>
    <!--end::App Wrapper-->

    <!--begin::Script-->
    <script
      src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
      crossorigin="anonymous"
    ></script>
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      crossorigin="anonymous"
    ></script>
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"
      crossorigin="anonymous"
    ></script>
    <script src="./js/adminlte.js"></script>

    <script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
        const isMobile = window.innerWidth <= 992;

        if (
          sidebarWrapper &&
          OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined &&
          !isMobile
        ) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
              theme: Default.scrollbarTheme,
              autoHide: Default.scrollbarAutoHide,
              clickScroll: Default.scrollbarClickScroll,
            },
          });
        }
      });
    </script>

    <script>
      const modalDelete = document.getElementById('modal-delete-user');
      modalDelete.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const nim = button.getAttribute('data-nim');
        document.getElementById('delete-nim').value = nim;
      });
    </script>

    <script>
      const modalEdit = document.getElementById('modal-edit-user');
      modalEdit.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const nim = button.getAttribute('data-nim');
        const name = button.getAttribute('data-nama');
        const nilaia = button.getAttribute('data-nilai');

        document.getElementById('edit-nim-lama').value = nim;
        document.getElementById('edit-nim').value = nim;
        document.getElementById('edit-nama').value = name;
        document.getElementById('edit-nilai').value = nilaia;
      });
    </script>
  </body>
  <!--end::Body-->
</html>
