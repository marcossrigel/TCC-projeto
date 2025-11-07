<?php
session_start();
session_unset();
session_destroy();
header("Location: https://www.getic.pe.gov.br/?p=auth_painel");
exit;