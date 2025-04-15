import unittest
import requests

class RJAutoberlesFullTests(unittest.TestCase):
    base_url = "http://localhost/R-J_autoberles"
    session_cookie = {"PHPSESSID": "valid-session-id"}  # Replace with a valid session ID
    admin_cookie = {"PHPSESSID": "valid-admin-session-id"}  # Replace with a valid admin session ID

    def test_homepage(self):
        response = requests.get(f"{self.base_url}/php/index.php")
        self.assertEqual(response.status_code, 200, "Homepage did not load successfully.")
        self.assertIn("R&J autókölcsönző", response.text, "Homepage content mismatch.")

    def test_registration(self):
        data = {
            "felhasznalo_nev": "testuser",
            "nev": "Test User",
            "emailcim": "testuser@example.com",
            "jelszo": "password123",
            "jelszo_ujra": "password123",
            "jogositvany_kiallitasDatum": "2023-01-01",
            "szamlazasi_cim": "Test Address",
        }
        response = requests.post(f"{self.base_url}/php/register.php", data=data)
        self.assertEqual(response.status_code, 200, "Registration endpoint failed.")
        self.assertIn("Sikeres regisztráció", response.text, "Registration response mismatch.")

    def test_login(self):
        data = {
            "felhasznalo_nev": "testuser",
            "jelszo": "password123",
        }
        response = requests.post(f"{self.base_url}/php/login.php", data=data)
        self.assertEqual(response.status_code, 200, "Login endpoint failed.")
        self.assertIn("Profilom", response.text, "Login response mismatch.")

    def test_vehicle_page(self):
        response = requests.get(f"{self.base_url}/php/jarmuvek.php")
        self.assertEqual(response.status_code, 200, "Vehicle page did not load successfully.")
        self.assertIn("Járművek", response.text, "Vehicle page content mismatch.")

    def test_car_rental(self):
        data = {
            "jarmu_id": 1,  # Replace with a valid car ID
            "name": "Test User",
            "email": "testuser@example.com",
            "phone": "+36201234567",
            "rental_date": "2023-12-01",
            "return_date": "2023-12-10",
            "fizetes_mod": 1,
        }
        response = requests.post(f"{self.base_url}/php/jarmuvek.php", data=data)
        self.assertEqual(response.status_code, 200, "Car rental endpoint failed.")
        self.assertIn("A bérlés sikeresen rögzítve", response.text, "Car rental response mismatch.")

    def test_submit_review(self):
        data = {
            "jarmu_id": 1,  # Replace with a valid car ID
            "message": "This is a test review.",
        }
        response = requests.post(f"{self.base_url}/php/cseveges.php", data=data)
        self.assertEqual(response.status_code, 200, "Submit review endpoint failed.")
        self.assertIn("Vélemény mentése sikeres", response.text, "Submit review response mismatch.")

    def test_contact_form(self):
        data = {
            "name": "Test User",
            "email": "testuser@example.com",
            "message": "This is a test message.",
        }
        response = requests.post(f"{self.base_url}/php/kapcsolat.php", data=data)
        self.assertEqual(response.status_code, 200, "Contact form endpoint failed.")
        self.assertIn("Köszönjük", response.text, "Contact form response mismatch.")

    def test_loyalty_points_page(self):
        response = requests.get(f"{self.base_url}/php/husegpontok.php", cookies=self.session_cookie)
        self.assertEqual(response.status_code, 200, "Loyalty points page did not load successfully.")
        self.assertIn("Hűségpontok", response.text, "Loyalty points page content mismatch.")

    def test_forum_page(self):
        response = requests.get(f"{self.base_url}/php/forum.php", cookies=self.session_cookie)
        self.assertEqual(response.status_code, 200, "Forum page did not load successfully.")
        self.assertIn("Fórum", response.text, "Forum page content mismatch.")

    def test_profile_page(self):
        response = requests.get(f"{self.base_url}/php/profilom.php", cookies=self.session_cookie)
        self.assertEqual(response.status_code, 200, "Profile page did not load successfully.")
        self.assertIn("Profilom", response.text, "Profile page content mismatch.")

    def test_logout(self):
        response = requests.get(f"{self.base_url}/php/logout.php", cookies=self.session_cookie)
        self.assertEqual(response.status_code, 200, "Logout endpoint failed.")
        self.assertIn("Bejelentkezés", response.text, "Logout response mismatch.")

    def test_admin_vehicle_management(self):
        response = requests.get(f"{self.base_url}/admin/php/autok_kezeles.php", cookies=self.admin_cookie)
        self.assertEqual(response.status_code, 200, "Admin vehicle management page did not load successfully.")
        self.assertIn("Jármű hozzáadása", response.text, "Admin vehicle management content mismatch.")

    def test_admin_rental_management(self):
        response = requests.get(f"{self.base_url}/admin/php/admin_berlesek.php", cookies=self.admin_cookie)
        self.assertEqual(response.status_code, 200, "Admin rental management page did not load successfully.")
        self.assertIn("Bérlések Kezelése", response.text, "Admin rental management content mismatch.")

    def test_admin_review_management(self):
        response = requests.get(f"{self.base_url}/admin/php/admin_velemenyek.php", cookies=self.admin_cookie)
        self.assertEqual(response.status_code, 200, "Admin review management page did not load successfully.")
        self.assertIn("Felhasználói vélemények", response.text, "Admin review management content mismatch.")

    def test_admin_user_management(self):
        response = requests.get(f"{self.base_url}/admin/php/admin_jogosultsag.php", cookies=self.admin_cookie)
        self.assertEqual(response.status_code, 200, "Admin user management page did not load successfully.")
        self.assertIn("Jogosultságok", response.text, "Admin user management content mismatch.")

    def test_admin_promotions_management(self):
        response = requests.get(f"{self.base_url}/admin/php/admin_akciok.php", cookies=self.admin_cookie)
        self.assertEqual(response.status_code, 200, "Admin promotions management page did not load successfully.")
        self.assertIn("Akciók kezelése", response.text, "Admin promotions management content mismatch.")

if __name__ == "__main__":
    unittest.main()
