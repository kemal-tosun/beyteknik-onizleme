<?php
// Teklif formunu info@beyteknik.com adresine gonderir (sitenin kendi sunucusundan).
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo '{"ok":false}'; exit; }
if (!empty($_POST['web'])) { echo '{"ok":true}'; exit; } // robot tuzagi

$ALICI = 'info@beyteknik.com';
$GONDEREN = 'site@beyteknik.com';

function temiz($k, $max = 200) {
  $v = isset($_POST[$k]) ? trim((string)$_POST[$k]) : '';
  $v = str_replace(["\r", "\0"], '', $v);
  return mb_substr($v, 0, $max, 'UTF-8');
}
$ad = temiz('ad'); $firma = temiz('firma'); $tel = temiz('tel', 40);
$eposta = temiz('eposta', 120); $alan = temiz('alan', 300); $mesaj = temiz('mesaj', 4000);

if ($ad === '' || $tel === '') { http_response_code(400); echo '{"ok":false}'; exit; }

$govde = "Siteden yeni teklif talebi\n\n"
  . "Ad soyad: $ad\nKurum: $firma\nTelefon: $tel\nE-posta: $eposta\nAlan: $alan\n\n"
  . "Mesaj:\n$mesaj\n\n---\nGonderim: " . date('d.m.Y H:i') . " / " . ($_SERVER['REMOTE_ADDR'] ?? '');

$konu = '=?UTF-8?B?' . base64_encode('Teklif talebi: ' . str_replace("\n", ' ', $ad)) . '?=';
$basliklar = "From: BeyTeknik Site <$GONDEREN>\r\n"
  . "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n";
if (filter_var($eposta, FILTER_VALIDATE_EMAIL)) $basliklar .= "Reply-To: $eposta\r\n";

$ok = @mail($ALICI, $konu, $govde, $basliklar, "-f$GONDEREN");
if (!$ok) http_response_code(500);
echo json_encode(['ok' => (bool)$ok]);
