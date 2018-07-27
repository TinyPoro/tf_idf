# Tf_Idf

### Sử dụng:
1.Hàm khởi tạo nhận 3 tham số `TF_IDF($language, $α = 0.5, $flag = true)`
- Mặc định  khi tính tf sẽ sử dụng công thức sau để giảm sự ảnh hưởng của các câu dài
```
tf(t, d) = α + (1 − α)(ft,d/max(ft',d))
```
- α mặc định sẽ là 0,5. Bạn có thể truyền tham số α vào hàm khởi tạo.
- Đặt $flag = false nếu bạn không muốn sử dụng công thức trên

2.Sử dụng hàm `addDocText` để thêm văn bản, hàm sẽ trả về `docId` tương ứng với văn bản bạn vừa thêm.

3.Sử dụng hàm `getTfIdf($term, $docId)` để lấy giá trị tf.idf của từ trong văn bản có id tương ứng.

4.Sử dụng hàm `getDocTfIdf($docId)` để lấy giá trị tf.idf của văn bản có id tương ứng.

### Lưu ý : Để có hiệu quả tốt
1. Chỉ tính tf.idf với các danh từ
2. Cộng thêm điểm cho các câu có chứa từ trong title.
3. Áp dụng trọng số vị trí vào giá trị điểm cho bước 2 theo thang điểm [0,1]

###### [Tham khảo](http://research.nii.ac.jp/ntcir/workshop/OnlineProceedings3/NTCIR3-TSC-SekiY.pdf)
