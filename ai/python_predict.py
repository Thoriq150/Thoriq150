import os
import joblib
import pymysql
import pandas as pd
import numpy as np
from datetime import date

# =====================================================
# LOAD MODEL
# =====================================================

BASE_DIR = os.path.dirname(os.path.abspath(__file__))

MODEL_PATH = os.path.join(BASE_DIR, "model_xgboost_umkm.pkl")
FEATURE_PATH = os.path.join(BASE_DIR, "feature_columns.pkl")

print("="*50)
print("LOADING MODEL")
print("="*50)

try:
    model = joblib.load(MODEL_PATH)
    feature_columns = joblib.load(FEATURE_PATH)

    print("Model berhasil dimuat.")
    print("Feature berhasil dimuat.")

except Exception as e:
    print(e)
    exit()

# =====================================================
# DATABASE
# =====================================================

print("="*50)
print("KONEKSI DATABASE")
print("="*50)

conn = pymysql.connect(
    host="localhost",
    user="root",
    password="",
    database="inventori_db",
    autocommit=False
)

cursor = conn.cursor()

print("Database berhasil terkoneksi.")

# =====================================================
# AMBIL DATA
# =====================================================

query = """
SELECT
    tk.tanggal,
    dt.barang_id,
    dt.jumlah
FROM detail_transaksi dt
INNER JOIN transaksi_keluar tk
ON dt.transaksi_keluar_id = tk.transaksi_keluar_id
ORDER BY tk.tanggal ASC
"""

df = pd.read_sql(query, conn)

print("Jumlah transaksi :", len(df))

# =====================================================
# PREPROCESSING
# =====================================================

df["tanggal"] = pd.to_datetime(df["tanggal"])

daily = (
    df
    .groupby(
        [
            "tanggal",
            "barang_id"
        ]
    )["jumlah"]
    .sum()
    .reset_index()
)

daily = daily.sort_values(
    [
        "barang_id",
        "tanggal"
    ]
)

# =====================================================
# FEATURE ENGINEERING
# =====================================================

daily["Lag_1"] = daily.groupby("barang_id")["jumlah"].shift(1)
daily["Lag_7"] = daily.groupby("barang_id")["jumlah"].shift(7)
daily["Lag_14"] = daily.groupby("barang_id")["jumlah"].shift(14)
daily["Lag_30"] = daily.groupby("barang_id")["jumlah"].shift(30)

daily["Rolling_7"] = (
    daily.groupby("barang_id")["jumlah"]
    .transform(
        lambda x: x.shift(1).rolling(7).mean()
    )
)

daily["Rolling_14"] = (
    daily.groupby("barang_id")["jumlah"]
    .transform(
        lambda x: x.shift(1).rolling(14).mean()
    )
)

daily["Rolling_30"] = (
    daily.groupby("barang_id")["jumlah"]
    .transform(
        lambda x: x.shift(1).rolling(30).mean()
    )
)

daily["Month"] = daily["tanggal"].dt.month
daily["DayOfWeek"] = daily["tanggal"].dt.dayofweek

daily = daily.dropna().reset_index(drop=True)

print("Jumlah data siap prediksi :", len(daily))

# =====================================================
# PREDIKSI
# =====================================================

X = daily[feature_columns]

prediksi = model.predict(X)

daily["hasil_prediksi"] = np.clip(
    np.round(prediksi),
    0,
    None
).astype(int)

print("Prediksi berhasil.")

# =====================================================
# AMBIL PREDIKSI TERBARU
# =====================================================

hasil = (
    daily
    .sort_values("tanggal")
    .groupby("barang_id")
    .tail(1)
)

print("Jumlah barang :", len(hasil))

# =====================================================
# HAPUS DATA LAMA
# =====================================================

cursor.execute("DELETE FROM prediksi_stok")

conn.commit()

print("Prediksi lama berhasil dihapus.")

# =====================================================
# INSERT HASIL BARU
# =====================================================

periode = date.today().strftime("%Y-%m")

tanggal_prediksi = date.today()

sql = """
INSERT INTO prediksi_stok
(
barang_id,
periode,
hasil_prediksi,
tanggal_prediksi,
user_id
)
VALUES
(
%s,
%s,
%s,
%s,
%s
)
"""

for _, row in hasil.iterrows():

    cursor.execute(
        sql,
        (
            int(row["barang_id"]),
            periode,
            int(row["hasil_prediksi"]),
            tanggal_prediksi,
            1
        )
    )

conn.commit()

print("Prediksi berhasil disimpan.")

print("="*50)

print(
    hasil[
        [
            "barang_id",
            "hasil_prediksi"
        ]
    ]
)

print("="*50)

cursor.close()
conn.close()

print("SELESAI")
