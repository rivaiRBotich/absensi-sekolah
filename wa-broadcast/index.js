import express from "express";
import qrcode from "qrcode-terminal";
import pino from "pino";
import {
  makeWASocket,
  useMultiFileAuthState,
  DisconnectReason,
} from "@whiskeysockets/baileys";

const PORT = process.env.PORT || 3010;
// Ganti dengan token rahasia kamu sendiri, dan simpan di .env, jangan hardcode di production
const API_TOKEN = process.env.WA_API_TOKEN || "ganti-dengan-token-rahasia";

let sock; // instance socket WhatsApp aktif
let isReady = false;

async function startWhatsApp() {
  // Sesi login disimpan di folder ./auth agar tidak perlu scan QR ulang tiap restart
  const { state, saveCreds } = await useMultiFileAuthState("./auth");

  sock = makeWASocket({
    auth: state,
    logger: pino({ level: "silent" }),
  });

  sock.ev.on("creds.update", saveCreds);

  sock.ev.on("connection.update", (update) => {
    const { connection, lastDisconnect, qr } = update;

    if (qr) {
      console.log("Scan QR ini dengan WhatsApp di HP kamu:");
      qrcode.generate(qr, { small: true });
    }

    if (connection === "open") {
      isReady = true;
      console.log("✅ WhatsApp terhubung dan siap kirim pesan.");
    }

    if (connection === "close") {
      isReady = false;
      const shouldReconnect =
        lastDisconnect?.error?.output?.statusCode !== DisconnectReason.loggedOut;

      console.log("Koneksi terputus. Reconnect?", shouldReconnect);
      if (shouldReconnect) startWhatsApp();
    }
  });
}

startWhatsApp();

const app = express();
app.use(express.json());

// Middleware autentikasi sederhana antara Laravel <-> service ini
app.use((req, res, next) => {
  const token = req.headers["authorization"]?.replace("Bearer ", "");
  if (token !== API_TOKEN) {
    return res.status(401).json({ message: "Token tidak valid" });
  }
  next();
});

// Endpoint yang dipanggil dari Laravel, menggantikan panggilan ke api.fonnte.com
app.post("/send-message", async (req, res) => {
  const { target, message } = req.body;

  if (!target || !message) {
    return res.status(400).json({ message: "target dan message wajib diisi" });
  }

  if (!isReady) {
    return res.status(503).json({ message: "WhatsApp belum terhubung. Coba lagi sebentar." });
  }

  try {
    // Format nomor: 08xxx -> 628xxx@s.whatsapp.net
    let number = target.replace(/\D/g, "");
    if (number.startsWith("0")) number = "62" + number.slice(1);
    const jid = `${number}@s.whatsapp.net`;

    await sock.sendMessage(jid, { text: message });

    return res.json({ status: true, message: "Pesan terkirim" });
  } catch (err) {
    return res.status(500).json({ status: false, message: err.message });
  }
});

app.get("/status", (req, res) => {
  res.json({ connected: isReady });
});

app.listen(PORT, () => {
  console.log(`WA broadcast service jalan di http://localhost:${PORT}`);
});
