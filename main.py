from fastapi import FastAPI, UploadFile, File, Form, HTTPException
import face_recognition
import numpy as np
import cv2
import json
import os
import io

app = FastAPI(title="Face Recognition & Liveness API")

def check_liveness(image_bytes: bytes) -> bool:
    try:
        nparr = np.frombuffer(image_bytes, np.uint8)
        img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
        if img is None:
            return False
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
        fm = cv2.Laplacian(gray, cv2.CV_64F).var()
        if fm < 40.0:  
            return False
        return True
    except Exception:
        return False

# @app.post("/recognize-face")
@app.post("/recognize-face")
async def recognize_face(
    captured_face_file: UploadFile = File(...),
    students_data: str = Form(...) 
):
    try:
        captured_bytes = await captured_face_file.read()

        # 1. Cek Liveness
        if not check_liveness(captured_bytes):
            raise HTTPException(status_code=400, detail="Liveness check failed: Indikasi Spoofing/Foto Cetak!")

        # 2. Decode wajah kamera
        captured_img = face_recognition.load_image_file(io.BytesIO(captured_bytes))
        captured_encodings = face_recognition.face_encodings(captured_img)

        if not captured_encodings:
            raise HTTPException(status_code=400, detail="Wajah tidak terdeteksi pada kamera")

        target_encoding = captured_encodings[0]
        students_list = json.loads(students_data)

        known_encodings = []
        valid_students = []

        # SESUAIKAN PATH INI DENGAN FOLDER STORAGE LARAVEL ANDA
        # STORAGE_DIR = "C:/Users/adria/Documents/data vai/web/absensi-sekolah/storage/app/public/" 
        STORAGE_DIR = "/var/www/absensi-sekolah/laravel-absensi-backend/storage/app/public/"
        
        for student in students_list:
            if not student.get("face_image_path"):
                continue
            
            file_path = os.path.join(STORAGE_DIR, student['face_image_path'])
            print(file_path)
            print(os.path.exists(file_path))
            if not os.path.exists(file_path):
                continue

            try:
                ref_img = face_recognition.load_image_file(file_path)
                ref_encs = face_recognition.face_encodings(ref_img)
                
                if ref_encs:
                    known_encodings.append(ref_encs[0])
                    valid_students.append(student)
            except Exception:
                continue

        if not known_encodings:
            raise HTTPException(status_code=404, detail="Tidak ada data referensi wajah siswa yang valid.")

        # 3. Bandingkan wajah
        matches = face_recognition.compare_faces(known_encodings, target_encoding, tolerance=0.5)
        face_distances = face_recognition.face_distance(known_encodings, target_encoding)

        if True in matches:
            best_match_index = np.argmin(face_distances)
            if matches[best_match_index]:
                matched_student = valid_students[best_match_index]
                return {
                    "status": "success",
                    "verified": True,
                    "student": matched_student,
                    "message": f"Wajah Cocok dengan {matched_student['name']}"
                }

        return {"status": "failed", "verified": False, "message": "Wajah tidak dikenali di dalam database sekolah."}

    except HTTPException as he:
        raise he
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Server Python Error: {str(e)}")
