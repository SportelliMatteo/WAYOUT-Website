<?php

http_response_code(503);
header('Content-Type: text/html; charset=UTF-8');
header('Retry-After: 600');
header('Cache-Control: no-store, max-age=0');

?><!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>WayOut — Aggiornamento in corso</title>
</head>
<body style="margin:0;min-height:100vh;display:grid;place-items:center;background:#111827;color:#fff;font:16px/1.5 system-ui,sans-serif">
    <main style="max-width:36rem;padding:2rem;text-align:center">
        <h1>Torniamo tra pochi minuti.</h1>
        <p>Stiamo completando un aggiornamento di WayOut. Riprova tra poco.</p>
    </main>
</body>
</html>
