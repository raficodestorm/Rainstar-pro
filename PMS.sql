-- Create the database
CREATE DATABASE IF NOT EXISTS `rainstar_pharma`;
USE `rainstar_pharma`;

-- 1. Role Table
CREATE TABLE role (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) UNIQUE NOT NULL
);

-- 2. users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    branch TEXT,
    password VARCHAR(255) NOT NULL,
    role_name VARCHAR(100) NOT NULL,
    image VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_name) REFERENCES role(role_name)
);

-- 3. Supplier Table
CREATE TABLE supplier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Medicine Type Table
CREATE TABLE medicine_type (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(100) NOT NULL
);

-- 5. Stock Table
CREATE TABLE stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medicine_name VARCHAR(100) NOT NULL,
    medicine_type_id INT,
    quantity INT DEFAULT 0,
    purchase_price DECIMAL(10,2),
    sale_price DECIMAL(10,2),
    expiry_date DATE,
    supplier_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medicine_type_id) REFERENCES medicine_type(id)
);

-- 6. Customers Table
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 7. Sales Table
CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    pharmacist_id INT,
    total_amount DECIMAL(10,2),
    discount VARCHAR(10),
    net_total DECIMAL(10,2),
    paid_amount DECIMAL(10,2),
    due DECIMAL(10,2),
    status VARCHAR(100),
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (pharmacist_id) REFERENCES users(id)
);

-- 8. Sale Items Table (Details of medicines sold in a sale)
CREATE TABLE sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT,
    stock_id INT,
    medicine VARCHAR(100),
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id),
    FOREIGN KEY (stock_id) REFERENCES stock(id)
);

-- 9. Return Table (Customer returns after sale)
CREATE TABLE return_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT,
    stock_id INT,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    reason TEXT,
    return_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(id),
    FOREIGN KEY (stock_id) REFERENCES stock(id)
);

-- 10. Purchases Table
CREATE TABLE purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(100),
    supplier_id INT,
    supplier_name VARCHAR(100),
    total_amount DECIMAL(10,2),
    pharmacist_name VARCHAR(100),
    purchase_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES supplier(id)
);

-- 11. Purchase Items Table
CREATE TABLE purchase_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_id INT,
    stock_id INT,
    quantity INT,
    unit_price DECIMAL(10,2),
    FOREIGN KEY (purchase_id) REFERENCES purchases(id),
    FOREIGN KEY (stock_id) REFERENCES stock(id)
);

-- 12. Purchase Return Table
CREATE TABLE purchase_return (
    id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_id INT,
    stock_id INT,
    quantity INT,
    reason TEXT,
    return_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (purchase_id) REFERENCES purchases(id),
    FOREIGN KEY (stock_id) REFERENCES stock(id)
);

-- 13. Expired Medicine Table
CREATE TABLE expired_medicine (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stock_id INT,
    expiry_date DATE,
    noted_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (stock_id) REFERENCES stock(id)
);
-- 14. expense Table 
CREATE TABLE expense (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    amount INT,
    purpose VARCHAR(50),
    description VARCHAR(200),
    spent_by VARCHAR(100),
    spent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- 15. expense Table 
CREATE TABLE revenue (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    amount INT,
    Date DATE DEFAULT (CURRENT_DATE)
);

-- ------------------- Insert default roles---------------------------------------
INSERT INTO role (role_name) VALUES ('admin'), ('pharmacist');
-- ------------------- Insert sample supplier---------------------------------------
INSERT INTO supplier (name, contact_person, phone, email, address) 
VALUES 
('Square Pharmaceuticals Ltd.', 'Rasel Hossain', '01910192276', 'mdraselhossain2276@gmail.com', 'Shahidnagar, Lalbag, Dhaka'),
('Beximco Pharmaceuticals Ltd.', 'Karim Uddin', '01711002233', 'karim@beximco.com', 'Tejgaon, Dhaka'),
('Incepta Pharmaceuticals Ltd.', 'Rahman Hossain', '01699887766', 'rahman@incepta.com', 'Uttara, Dhaka'),
('Renata Limited', 'Sabbir Khan', '01822334455', 'sabbir@renata.com', 'Mirpur, Dhaka'),
('ACME Laboratories Ltd.', 'Jahangir Alam', '01555667788', 'jahangir@acme.com', 'Mohakhali, Dhaka'),
('Healthcare Pharmaceuticals Ltd.', 'Tanvir Hasan', '01987654321', 'tanvir@healthcare.com', 'Banani, Dhaka'),
('Opsonin Pharma Ltd.', 'Hasan Mahmud', '01733445566', 'hasan@opsonin.com', 'Motijheel, Dhaka'),
('Eskayef Pharmaceuticals Ltd.', 'Alamgir Hossain', '01899887744', 'alamgir@eskayef.com', 'Dhanmondi, Dhaka'),
('Aristopharma Ltd.', 'Samiul Islam', '01655667799', 'samiul@aristo.com', 'Farmgate, Dhaka'),
('Popular Pharmaceuticals Ltd.', 'Mizanur Rahman', '01588997766', 'mizan@popular.com', 'Shyamoli, Dhaka'),
('ACI Limited', 'Shuvo Chowdhury', '01776554433', 'shuvo@aci.com', 'Kawran Bazar, Dhaka'),
('General Pharmaceuticals Ltd.', 'Fahim Reza', '01811223344', 'fahim@general.com', 'Gulshan, Dhaka'),
('Radiant Pharmaceuticals Ltd.', 'Arif Hasan', '01933445577', 'arif@radiant.com', 'Rampura, Dhaka'),
('Drug International Ltd.', 'Rony Akter', '01522334455', 'rony@drugint.com', 'Khilgaon, Dhaka'),
('Delta Pharma Ltd.', 'Mahmudul Hasan', '01677889911', 'mahmud@delta.com', 'Jatrabari, Dhaka'),
('Sharif Pharmaceuticals Ltd.', 'Shahadat Hossain', '01766778899', 'shahadat@sharif.com', 'Keraniganj, Dhaka'),
('Navana Pharmaceuticals Ltd.', 'Imran Ali', '01833445566', 'imran@navana.com', 'Baridhara, Dhaka'),
('Nuvista Pharma Ltd.', 'Zahid Hasan', '01911223344', 'zahid@nuvista.com', 'Badda, Dhaka'),
('Pacific Pharmaceuticals Ltd.', 'Sohel Rana', '01799887755', 'sohel@pacific.com', 'Malibagh, Dhaka'),
('IBN SINA Pharmaceuticals', 'S A Rafi', '01603922706', 'sarafi3258@gmail.com', 'Lalbag, Dhaka');

-- ------------------- Insert medicine type---------------------------------------
INSERT INTO medicine_type (type_name) VALUES
('Paracetamol'),('Ibuprofen'),('Aspirin'),('Diclofenac'),('Naproxen'),('Omeprazole'),('Esomeprazole'),('Pantoprazole'),('Rabeprazole'),('Lansoprazole'),('Amoxicillin'),('Cefixime'),('Cefuroxime'),('Ceftriaxone'),('Azithromycin'),('Clarithromycin'),('Metronidazole'),('Ciprofloxacin'),
('Levofloxacin'),('Moxifloxacin'),('Doxycycline'),('Tetracycline'),('Clindamycin'),('Linezolid'),('Vancomycin'),('Insulin'),('Metformin'),('Gliclazide'),('Glimepiride'),('Sitagliptin'),('Vildagliptin'),('Linagliptin'),('Losartan'),('Telmisartan'),
('Olmesartan'),('Valsartan'),('Amlodipine'),('Nifedipine'),('Bisoprolol'),('Metoprolol'),('Propranolol'),('Atenolol'),('Furosemide'),('Spironolactone'),('Hydrochlorothiazide'),('Atorvastatin'),
('Rosuvastatin'),('Simvastatin'),('Salbutamol'),('Montelukast'),('Calcium'),('Magnesium'),('Zinc'),('Vitamin A'),('Vitamin B'),('Iron');
