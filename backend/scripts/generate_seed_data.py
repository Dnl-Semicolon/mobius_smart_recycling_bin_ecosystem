#!/usr/bin/env python3
"""
Mobius Smart Recycling Bin Ecosystem — Dense Seed Data Generator

Generates 3 years (2024-01-01 to 2026-12-31) of realistic data across
3 brands (ZUS Coffee, Starbucks, MIXUE) with growth curves.

Usage:
    python3 scripts/generate_seed_data.py --dialect sqlite --output database/seed_data.sql
    python3 scripts/generate_seed_data.py --dialect mysql --output database/seed_data.sql
"""

import argparse
import random
import hashlib
import string
from datetime import datetime, timedelta
from collections import defaultdict

# ─── Fixed seed for reproducibility ──────────────────────────────────────
RANDOM_SEED = 42
random.seed(RANDOM_SEED)

# ─── Pre-computed bcrypt hash of "password" ──────────────────────────────
# Generated via: php artisan tinker --execute="echo Hash::make('password');"
PASSWORD_HASH = "$2y$12$Qwg8IAg6qDVcMptwX99LC.LoXUu0T0iarJVxWOPQXmAHIL5PX/xoO"

# ─── Waste type config ───────────────────────────────────────────────────
WASTE_TYPES = ["paper_cup", "plastic_cup", "lid", "straw", "napkin", "liquid_waste"]
WASTE_WEIGHTS = [0.35, 0.25, 0.15, 0.10, 0.05, 0.10]
WASTE_POINTS = {
    "paper_cup": 15, "plastic_cup": 12, "lid": 5,
    "straw": 3, "napkin": 2, "liquid_waste": 8,
}
WASTE_FILL_INCREMENT = {
    "paper_cup": 5, "plastic_cup": 5, "lid": 2,
    "straw": 1, "napkin": 1, "liquid_waste": 3,
}
CUP_TYPES = {"paper_cup", "plastic_cup"}

# ─── Report types ────────────────────────────────────────────────────────
REPORT_TYPES = ["damaged_bin", "wrong_detection", "overflow", "contamination", "other"]
REPORT_STATUSES = ["open", "investigating", "resolved", "closed"]

# ─── Brand config ────────────────────────────────────────────────────────
BRANDS = [
    {"id": 1, "name": "ZUS Coffee",  "slug": "zus-coffee",  "color": "#1A1A1A", "multiplier": 1.40, "budget": 30000,
     "desc": "Specialty coffee made accessible. 500+ outlets across Malaysia."},
    {"id": 2, "name": "Starbucks",   "slug": "starbucks",   "color": "#00704A", "multiplier": 1.50, "budget": 50000,
     "desc": "Global coffeehouse chain committed to ethical sourcing and environmental stewardship."},
    {"id": 3, "name": "MIXUE",       "slug": "mixue",       "color": "#E8352E", "multiplier": 1.30, "budget": 25000,
     "desc": "Chinese ice cream and tea chain. Affordable drinks, massive scale."},
]

# ─── Malaysian locations with real coordinates ───────────────────────────
OUTLET_LOCATIONS = [
    # Penang (core market)
    {"name": "ZUS Coffee Gurney Plaza",           "brand_id": 1, "addr": "Gurney Plaza, Persiaran Gurney, 10250 George Town, Penang",         "lat": 5.4370, "lng": 100.3100, "phase": 1},
    {"name": "ZUS Coffee Queensbay Mall",          "brand_id": 1, "addr": "Queensbay Mall, 11900 Bayan Lepas, Penang",                        "lat": 5.3328, "lng": 100.3064, "phase": 1},
    {"name": "ZUS Coffee Tanjung Bungah",          "brand_id": 1, "addr": "88-G-A05, Tree Square, 11200 Tanjung Bungah, Penang",              "lat": 5.4641, "lng": 100.2840, "phase": 2},
    {"name": "ZUS Coffee Komtar",                  "brand_id": 1, "addr": "Komtar Walk, Jalan Penang, 10000 George Town, Penang",             "lat": 5.4147, "lng": 100.3308, "phase": 2},
    {"name": "Starbucks Reserve Gurney Plaza",     "brand_id": 2, "addr": "Gurney Plaza, Persiaran Gurney, 10250 George Town, Penang",         "lat": 5.4372, "lng": 100.3098, "phase": 1},
    {"name": "Starbucks Queensbay Mall",           "brand_id": 2, "addr": "Queensbay Mall, 11900 Bayan Lepas, Penang",                        "lat": 5.3330, "lng": 100.3070, "phase": 1},
    {"name": "Starbucks Straits Quay",             "brand_id": 2, "addr": "Straits Quay, Jalan Seri Tanjung Pinang, 10470 Tanjung Tokong",    "lat": 5.4560, "lng": 100.3100, "phase": 3},
    {"name": "MIXUE Tanjung Tokong",               "brand_id": 3, "addr": "Jalan Tanjung Tokong, 10470 Tanjung Tokong, Penang",               "lat": 5.4450, "lng": 100.3020, "phase": 1},
    {"name": "MIXUE Jelutong",                     "brand_id": 3, "addr": "Jalan Jelutong, 11600 Jelutong, Penang",                           "lat": 5.3980, "lng": 100.3200, "phase": 2},
    {"name": "MIXUE Bayan Lepas",                  "brand_id": 3, "addr": "Bayan Lepas, 11900 Penang",                                        "lat": 5.3100, "lng": 100.2800, "phase": 3},
    # Kuala Lumpur (expansion)
    {"name": "ZUS Coffee Pavilion KL",             "brand_id": 1, "addr": "Pavilion KL, 168 Jalan Bukit Bintang, 55100 KL",                   "lat": 3.1489, "lng": 101.7131, "phase": 2},
    {"name": "ZUS Coffee Mid Valley",              "brand_id": 1, "addr": "Mid Valley Megamall, Lingkaran Syed Putra, 59200 KL",              "lat": 3.1178, "lng": 101.6774, "phase": 3},
    {"name": "ZUS Coffee Bangsar South",           "brand_id": 1, "addr": "The Sphere, Bangsar South, 59200 KL",                              "lat": 3.1108, "lng": 101.6655, "phase": 4},
    {"name": "Starbucks KLCC",                     "brand_id": 2, "addr": "Suria KLCC, Jalan Ampang, 50088 KL",                               "lat": 3.1579, "lng": 101.7116, "phase": 2},
    {"name": "Starbucks Mid Valley",               "brand_id": 2, "addr": "Mid Valley Megamall, 59200 KL",                                    "lat": 3.1180, "lng": 101.6778, "phase": 3},
    {"name": "Starbucks 1 Utama",                  "brand_id": 2, "addr": "1 Utama Shopping Centre, 47800 Petaling Jaya",                     "lat": 3.1509, "lng": 101.6154, "phase": 4},
    {"name": "MIXUE Sunway Pyramid",               "brand_id": 3, "addr": "Sunway Pyramid, 46150 Petaling Jaya",                              "lat": 3.0733, "lng": 101.6073, "phase": 2},
    {"name": "MIXUE Bukit Bintang",                "brand_id": 3, "addr": "Jalan Bukit Bintang, 55100 KL",                                    "lat": 3.1466, "lng": 101.7108, "phase": 3},
    {"name": "MIXUE Cheras",                       "brand_id": 3, "addr": "EkoCheras Mall, Jalan Cheras, 56000 KL",                            "lat": 3.0893, "lng": 101.7469, "phase": 4},
    # Johor (growth)
    {"name": "ZUS Coffee JB City Square",          "brand_id": 1, "addr": "Johor Bahru City Square, 80000 Johor Bahru",                       "lat": 1.4615, "lng": 103.7613, "phase": 3},
    {"name": "Starbucks JB City Square",           "brand_id": 2, "addr": "Johor Bahru City Square, 80000 Johor Bahru",                       "lat": 1.4617, "lng": 103.7615, "phase": 3},
    {"name": "MIXUE JB City Square",               "brand_id": 3, "addr": "Johor Bahru City Square, 80000 Johor Bahru",                       "lat": 1.4613, "lng": 103.7611, "phase": 4},
    # Ipoh (maturity)
    {"name": "ZUS Coffee Ipoh Parade",             "brand_id": 1, "addr": "Ipoh Parade, Jalan Sultan Abdul Jalil, 30100 Ipoh",                "lat": 4.5841, "lng": 101.0900, "phase": 4},
    {"name": "Starbucks Ipoh Parade",              "brand_id": 2, "addr": "Ipoh Parade, 30100 Ipoh",                                          "lat": 4.5843, "lng": 101.0902, "phase": 4},
    # Melaka
    {"name": "ZUS Coffee Dataran Pahlawan",        "brand_id": 1, "addr": "Dataran Pahlawan Megamall, 75000 Melaka",                          "lat": 2.1896, "lng": 102.2501, "phase": 4},
    {"name": "MIXUE Melaka Raya",                  "brand_id": 3, "addr": "Jalan Melaka Raya, 75000 Melaka",                                  "lat": 2.1910, "lng": 102.2520, "phase": 5},
    # Kota Kinabalu
    {"name": "Starbucks Imago KK",                 "brand_id": 2, "addr": "Imago Shopping Mall, KK Times Square, 88100 Kota Kinabalu",        "lat": 5.9749, "lng": 116.0724, "phase": 5},
    {"name": "ZUS Coffee Imago KK",                "brand_id": 1, "addr": "Imago Shopping Mall, 88100 Kota Kinabalu",                         "lat": 5.9751, "lng": 116.0726, "phase": 5},
    # Kuching
    {"name": "Starbucks Spring Mall Kuching",      "brand_id": 2, "addr": "The Spring Shopping Mall, 93300 Kuching",                           "lat": 1.5333, "lng": 110.3593, "phase": 5},
    {"name": "MIXUE Kuching CityOne",              "brand_id": 3, "addr": "CityOne Megamall, 93350 Kuching",                                  "lat": 1.5350, "lng": 110.3610, "phase": 5},
    # Extra Penang outlets (late stage)
    {"name": "ZUS Coffee Automall",                "brand_id": 1, "addr": "Automall, Jalan Perusahaan, 13600 Prai, Penang",                   "lat": 5.3616, "lng": 100.3970, "phase": 4},
    {"name": "Starbucks Automall Prai",            "brand_id": 2, "addr": "Automall, Jalan Perusahaan, 13600 Prai, Penang",                   "lat": 5.3618, "lng": 100.3972, "phase": 4},
    {"name": "MIXUE Prai",                         "brand_id": 3, "addr": "Jalan Baru, 13700 Prai, Penang",                                   "lat": 5.3620, "lng": 100.3975, "phase": 4},
    # Late additions
    {"name": "ZUS Coffee Setia City Mall",         "brand_id": 1, "addr": "Setia City Mall, 40170 Shah Alam",                                 "lat": 3.0837, "lng": 101.5768, "phase": 5},
    {"name": "Starbucks IOI City Mall",            "brand_id": 2, "addr": "IOI City Mall, 62502 Putrajaya",                                   "lat": 2.9700, "lng": 101.7119, "phase": 5},
    {"name": "MIXUE IOI City Mall",                "brand_id": 3, "addr": "IOI City Mall, 62502 Putrajaya",                                   "lat": 2.9702, "lng": 101.7121, "phase": 5},
    {"name": "ZUS Coffee Gurney Paragon",          "brand_id": 1, "addr": "Gurney Paragon Mall, 10250 George Town, Penang",                   "lat": 5.4380, "lng": 100.3110, "phase": 5},
    {"name": "Starbucks Penang Airport",           "brand_id": 2, "addr": "Penang International Airport, 11900 Bayan Lepas",                  "lat": 5.2970, "lng": 100.2770, "phase": 5},
    {"name": "MIXUE Ayer Itam",                    "brand_id": 3, "addr": "Jalan Ayer Itam, 11500 Ayer Itam, Penang",                          "lat": 5.3970, "lng": 100.2840, "phase": 5},
]

# ─── Malaysian names ─────────────────────────────────────────────────────
MALAY_FIRST = ["Ahmad", "Ali", "Amir", "Aziz", "Farid", "Hafiz", "Ibrahim", "Ismail", "Kamal", "Malik",
               "Nabil", "Omar", "Razak", "Syed", "Tariq", "Wan", "Yusuf", "Zain", "Amin", "Danial"]
MALAY_LAST = ["bin Abdullah", "bin Ahmad", "bin Hassan", "bin Ibrahim", "bin Ismail", "bin Mohamad",
              "bin Omar", "bin Razak", "bin Yusof", "bin Zainol"]
CHINESE_FIRST = ["Wei", "Jie", "Xin", "Yi", "Hui", "Jun", "Ming", "Ling", "Fang", "Li",
                 "Kai", "Hao", "Yan", "Chen", "Mei", "Shu", "Wen", "Zhi", "Hong", "Pei"]
CHINESE_LAST = ["Tan", "Lim", "Lee", "Wong", "Ng", "Chan", "Ong", "Goh", "Yeoh", "Cheah",
                "Teh", "Low", "Koh", "Chin", "Ho", "Foo", "Yap", "Ooi", "Khoo", "Teoh"]
INDIAN_FIRST = ["Arun", "Deepak", "Ganesh", "Hari", "Karthik", "Mohan", "Naren", "Prakash", "Rajan", "Suresh",
                "Vijay", "Anand", "Dinesh", "Gopal", "Kumar", "Naveen", "Priya", "Rani", "Sita", "Uma"]
INDIAN_LAST = ["Krishnan", "Muthu", "Nair", "Pillai", "Raman", "Rao", "Sharma", "Singh", "Subramanian", "Velu"]


def random_malaysian_name():
    r = random.random()
    if r < 0.45:
        return f"{random.choice(MALAY_FIRST)} {random.choice(MALAY_LAST)}"
    elif r < 0.80:
        return f"{random.choice(CHINESE_LAST)} {random.choice(CHINESE_FIRST)} {random.choice(CHINESE_FIRST)}"
    else:
        return f"{random.choice(INDIAN_FIRST)} {random.choice(INDIAN_LAST)}"


def random_email(name, domain="mobius.test"):
    clean = name.lower().replace(" ", ".").replace("bin.", "").replace("..", ".")
    suffix = random.randint(1, 9999)
    return f"{clean}{suffix}@{domain}"


def random_phone():
    prefix = random.choice(["010", "011", "012", "013", "014", "016", "017", "018", "019"])
    return f"{prefix}-{random.randint(100,999)} {random.randint(1000,9999)}"


# ─── Growth phases → date ranges ────────────────────────────────────────
# Phase 1: 2024-Q1 (Jan-Mar), Phase 2: 2024-Q3 (Jul-Sep), Phase 3: 2025-Q1, Phase 4: 2025-Q3, Phase 5: 2026-Q1
PHASE_START = {
    1: datetime(2024, 1, 15),
    2: datetime(2024, 7, 1),
    3: datetime(2025, 1, 1),
    4: datetime(2025, 7, 1),
    5: datetime(2026, 1, 1),
}
END_DATE = datetime(2026, 12, 31)

# ─── Detections per week by quarter ──────────────────────────────────────
# Maps (year, quarter) → approximate detections per week
WEEKLY_DETECTIONS = {
    (2024, 1): 50,  (2024, 2): 80,  (2024, 3): 200, (2024, 4): 300,
    (2025, 1): 500, (2025, 2): 700, (2025, 3): 1000, (2025, 4): 1200,
    (2026, 1): 1400, (2026, 2): 1500, (2026, 3): 1500, (2026, 4): 1500,
}


def get_quarter(dt):
    return (dt.month - 1) // 3 + 1


def sql_str(s, dialect):
    """Escape a string for SQL."""
    if s is None:
        return "NULL"
    s = str(s).replace("'", "''")
    return f"'{s}'"


def sql_ts(dt, dialect):
    """Format a datetime for SQL."""
    if dt is None:
        return "NULL"
    return f"'{dt.strftime('%Y-%m-%d %H:%M:%S')}'"


def sql_bool(b, dialect):
    if dialect == "mysql":
        return "1" if b else "0"
    return "1" if b else "0"


def sql_json(obj, dialect):
    import json
    s = json.dumps(obj)
    return sql_str(s, dialect)


def generate(dialect="sqlite", output_path=None):
    lines = []
    lines.append(f"-- Mobius Seed Data (generated {datetime.now().isoformat()})")
    lines.append(f"-- Dialect: {dialect}")
    lines.append(f"-- Random seed: {RANDOM_SEED}")
    lines.append("")

    if dialect == "sqlite":
        lines.append("PRAGMA foreign_keys = OFF;")
        lines.append("PRAGMA journal_mode = WAL;")
    elif dialect == "mysql":
        lines.append("SET FOREIGN_KEY_CHECKS = 0;")
        lines.append("SET NAMES utf8mb4;")
    lines.append("")

    # ─── 1. BRANDS ───────────────────────────────────────────────────
    lines.append("-- Brands")
    for b in BRANDS:
        lines.append(
            f"INSERT INTO brands (id, name, slug, logo_path, primary_color, description, "
            f"points_multiplier, rewards_budget, active, status, created_at, updated_at) VALUES ("
            f"{b['id']}, {sql_str(b['name'], dialect)}, {sql_str(b['slug'], dialect)}, NULL, "
            f"{sql_str(b['color'], dialect)}, {sql_str(b['desc'], dialect)}, "
            f"{b['multiplier']:.2f}, {b['budget']}, {sql_bool(True, dialect)}, 'active', "
            f"{sql_ts(datetime(2024,1,1), dialect)}, {sql_ts(datetime(2024,1,1), dialect)});"
        )
    lines.append("")

    # ─── 2. USERS ────────────────────────────────────────────────────
    lines.append("-- Users")
    users = []
    user_id = 0

    # Admin users (3)
    for i, (name, email) in enumerate([
        ("Admin", "admin@mobius.test"),
        ("System Admin", "sysadmin@mobius.test"),
        ("Operations Manager", "ops@mobius.test"),
    ]):
        user_id += 1
        users.append({"id": user_id, "name": name, "email": email, "roles": ["admin"],
                       "created_at": datetime(2024, 1, 1) + timedelta(days=i)})

    # Daniel Tan — multi-role test account (always present)
    user_id += 1
    users.append({"id": user_id, "name": "Daniel Tan", "email": "daniel@mobius.test",
                   "roles": ["public_user", "store_owner", "collector", "admin"],
                   "created_at": datetime(2024, 1, 1)})

    # Collectors (15)
    for i in range(15):
        user_id += 1
        name = random_malaysian_name()
        created = datetime(2024, 1, 1) + timedelta(days=random.randint(0, 365))
        users.append({"id": user_id, "name": name, "email": random_email(name),
                       "roles": ["collector"], "created_at": created})

    # Store owners (10)
    for i in range(10):
        user_id += 1
        name = random_malaysian_name()
        created = datetime(2024, 1, 1) + timedelta(days=random.randint(0, 500))
        users.append({"id": user_id, "name": name, "email": random_email(name),
                       "roles": ["store_owner"], "created_at": created})

    # Agency admins (2)
    for i in range(2):
        user_id += 1
        name = random_malaysian_name()
        created = datetime(2025, 1, 1) + timedelta(days=random.randint(0, 180))
        users.append({"id": user_id, "name": name, "email": random_email(name),
                       "roles": ["agency_admin"], "created_at": created})

    # Public users (70)
    for i in range(70):
        user_id += 1
        name = random_malaysian_name()
        # Stagger creation over 3 years with more later
        day_offset = int(random.betavariate(2, 5) * 1095)  # skew toward later
        created = datetime(2024, 1, 1) + timedelta(days=day_offset)
        users.append({"id": user_id, "name": name, "email": random_email(name),
                       "roles": ["public_user"], "created_at": created})

    # Generate unique usernames from names
    seen_usernames = set()
    for u in users:
        base = u["name"].lower().replace(" ", "_").replace(".", "")
        base = "".join(c for c in base if c.isalnum() or c == "_")
        username = base
        suffix = 1
        while username in seen_usernames:
            username = f"{base}{suffix}"
            suffix += 1
        seen_usernames.add(username)
        u["username"] = username

    for u in users:
        roles_json = sql_json(u["roles"], dialect)
        lines.append(
            f"INSERT INTO users (id, name, username, email, roles, email_verified_at, password, "
            f"points_balance, current_streak, longest_streak, created_at, updated_at) VALUES ("
            f"{u['id']}, {sql_str(u['name'], dialect)}, {sql_str(u['username'], dialect)}, {sql_str(u['email'], dialect)}, "
            f"{roles_json}, {sql_ts(u['created_at'], dialect)}, {sql_str(PASSWORD_HASH, dialect)}, "
            f"0, 0, 0, {sql_ts(u['created_at'], dialect)}, {sql_ts(u['created_at'], dialect)});"
        )
    lines.append("")

    # Separate user lists by role
    admin_ids = [u["id"] for u in users if "admin" in u["roles"]]
    collector_ids = [u["id"] for u in users if "collector" in u["roles"]]
    public_ids = [u["id"] for u in users if "public_user" in u["roles"]]
    user_created_map = {u["id"]: u["created_at"] for u in users}

    # ─── 3. OUTLETS ──────────────────────────────────────────────────
    lines.append("-- Outlets")
    outlets = []
    for i, loc in enumerate(OUTLET_LOCATIONS):
        outlet_id = i + 1
        created = PHASE_START[loc["phase"]]
        outlets.append({"id": outlet_id, **loc, "created_at": created})
        lines.append(
            f"INSERT INTO outlets (id, brand_id, name, address, latitude, longitude, "
            f"contact_name, contact_phone, operating_hours, contract_status, "
            f"created_at, updated_at) VALUES ("
            f"{outlet_id}, {loc['brand_id']}, {sql_str(loc['name'], dialect)}, "
            f"{sql_str(loc['addr'], dialect)}, {loc['lat']:.8f}, {loc['lng']:.8f}, "
            f"{sql_str(random_malaysian_name(), dialect)}, {sql_str(random_phone(), dialect)}, "
            f"NULL, 'active', "
            f"{sql_ts(created, dialect)}, {sql_ts(created, dialect)});"
        )
    lines.append("")

    # ─── 4. BINS ─────────────────────────────────────────────────────
    lines.append("-- Bins (2 per outlet)")
    bins = []
    bin_id = 0
    for outlet in outlets:
        for j in range(2):
            bin_id += 1
            sn = f"MBR-{outlet['created_at'].year}-{bin_id:04d}"
            bins.append({"id": bin_id, "serial_number": sn, "outlet_id": outlet["id"],
                         "brand_id": outlet["brand_id"], "created_at": outlet["created_at"],
                         "fill_level": 0})
            lines.append(
                f"INSERT INTO bins (id, serial_number, fill_level, status, "
                f"created_at, updated_at) VALUES ("
                f"{bin_id}, {sql_str(sn, dialect)}, 0, 'active', "
                f"{sql_ts(outlet['created_at'], dialect)}, {sql_ts(outlet['created_at'], dialect)});"
            )
    lines.append("")

    # ─── 5. BIN ASSIGNMENTS ──────────────────────────────────────────
    lines.append("-- Bin assignments")
    for b in bins:
        lines.append(
            f"INSERT INTO bin_assignments (bin_id, outlet_id, assigned_at) VALUES ("
            f"{b['id']}, {b['outlet_id']}, {sql_ts(b['created_at'], dialect)});"
        )
    lines.append("")

    # ─── 6. REWARDS ──────────────────────────────────────────────────
    lines.append("-- Rewards (5 per brand)")
    reward_id = 0
    rewards_data = [
        # ZUS
        (1, "Free Americano", "Hot or iced ZUS Americano.", 100, 60),
        (1, "RM3 Off Any Latte", "Applies to any latte variant.", 80, None),
        (1, "ZUS Tumbler", "Limited edition reusable tumbler.", 500, 20),
        (1, "Free Espresso", "Single or double shot.", 60, 100),
        (1, "Buy 1 Free 1 Voucher", "Valid for any drink.", 250, 30),
        # Starbucks
        (2, "Free Tall Drink", "Any handcrafted Tall beverage.", 200, 50),
        (2, "RM5 Off Next Visit", "RM5 discount on your next purchase.", 100, 100),
        (2, "Starbucks Tote Bag", "Limited edition reusable tote.", 500, 20),
        (2, "Free Pastry", "Any pastry with drink purchase.", 120, 80),
        (2, "Starbucks Mug", "Collectible ceramic mug.", 400, 15),
        # MIXUE
        (3, "Free Sundae", "Any signature ice cream sundae.", 80, 100),
        (3, "RM2 Off Any Drink", "Valid for any drink.", 50, None),
        (3, "MIXUE Water Bottle", "Branded reusable water bottle.", 300, 25),
        (3, "Free Milk Tea", "Any classic milk tea.", 90, 80),
        (3, "Ice Cream Combo", "Two sundaes for one.", 150, 40),
    ]
    for brand_id, name, desc, cost, stock in rewards_data:
        reward_id += 1
        stock_sql = str(stock) if stock is not None else "NULL"
        lines.append(
            f"INSERT INTO rewards (id, brand_id, name, description, points_cost, stock, "
            f"active, sort_order, created_at, updated_at) VALUES ("
            f"{reward_id}, {brand_id}, {sql_str(name, dialect)}, {sql_str(desc, dialect)}, "
            f"{cost}, {stock_sql}, {sql_bool(True, dialect)}, {reward_id}, "
            f"{sql_ts(datetime(2024,2,1), dialect)}, {sql_ts(datetime(2024,2,1), dialect)});"
        )
    lines.append("")

    # ─── 7. DETECTION EVENTS + RECYCLING TRANSACTIONS ────────────────
    lines.append("-- Detection events & recycling transactions")
    detection_id = 0
    transaction_id = 0
    pickup_id = 0
    redemption_id = 0
    notification_id = 0
    report_id = 0

    # Track per-user state for streaks
    user_points = defaultdict(int)
    user_streaks = defaultdict(int)
    user_longest = defaultdict(int)
    user_last_recycled = {}

    # Track bin fill levels for pickup triggers
    bin_fill = {b["id"]: 0 for b in bins}
    bin_brand = {b["id"]: b["brand_id"] for b in bins}

    # Generate day by day
    current_date = datetime(2024, 1, 15)
    active_bins_at_date = []
    pickups = []
    transactions = []
    detections_batch = []
    redemptions = []
    notifications = []
    reports = []

    while current_date <= END_DATE:
        year = current_date.year
        q = get_quarter(current_date)
        weekly_target = WEEKLY_DETECTIONS.get((year, q), 50)
        daily_target = weekly_target / 7.0

        # Weekend dip
        if current_date.weekday() >= 5:
            daily_target *= 0.6

        # Get bins active by this date
        active_bins_at_date = [b for b in bins if b["created_at"] <= current_date]
        if not active_bins_at_date:
            current_date += timedelta(days=1)
            continue

        # Get public users active by this date
        active_public = [uid for uid in public_ids if user_created_map[uid] <= current_date]
        if not active_public:
            current_date += timedelta(days=1)
            continue

        num_detections = max(1, int(random.gauss(daily_target, daily_target * 0.2)))

        for _ in range(num_detections):
            detection_id += 1
            b = random.choice(active_bins_at_date)
            wt = random.choices(WASTE_TYPES, weights=WASTE_WEIGHTS, k=1)[0]
            confidence = max(50, min(99, int(random.gauss(85, 8))))
            weight_g = random.randint(5, 250) if wt in CUP_TYPES else random.randint(1, 50)

            # User attribution: ~75% have user, ~25% anonymous
            user_id_val = None
            if random.random() < 0.75 and active_public:
                user_id_val = random.choice(active_public)

            # Brand detection on cups
            detected_brand_id = None
            if wt in CUP_TYPES:
                r = random.random()
                if r < 0.60:
                    detected_brand_id = b["brand_id"]  # match
                elif r < 0.80:
                    others = [br["id"] for br in BRANDS if br["id"] != b["brand_id"]]
                    detected_brand_id = random.choice(others)  # competitor
                # else: None (undetected)

            # Random time during business hours
            hour = random.choices(range(7, 23), weights=[
                1, 5, 8, 10, 10, 8, 10, 10, 8, 5, 8, 10, 8, 5, 3, 1
            ], k=1)[0]
            minute = random.randint(0, 59)
            detected_at = current_date.replace(hour=hour, minute=minute, second=random.randint(0, 59))

            uid_sql = str(user_id_val) if user_id_val else "NULL"
            dbid_sql = str(detected_brand_id) if detected_brand_id else "NULL"

            detections_batch.append(
                f"INSERT INTO detection_events (id, bin_id, user_id, waste_type, confidence, "
                f"weight_g, detected_brand_id, feedback_accurate, detected_at, created_at, updated_at) VALUES ("
                f"{detection_id}, {b['id']}, {uid_sql}, {sql_str(wt, dialect)}, {confidence}, "
                f"{weight_g}, {dbid_sql}, NULL, "
                f"{sql_ts(detected_at, dialect)}, {sql_ts(detected_at, dialect)}, {sql_ts(detected_at, dialect)});"
            )

            # Update bin fill level
            bin_fill[b["id"]] = min(100, bin_fill[b["id"]] + WASTE_FILL_INCREMENT.get(wt, 1))

            # Points calculation (mirrors DetectionService::calculatePoints)
            if user_id_val:
                base_pts = WASTE_POINTS.get(wt, 5)
                brand_mult = next((br["multiplier"] for br in BRANDS if br["id"] == b["brand_id"]), 1.0)

                if wt in CUP_TYPES:
                    if detected_brand_id is None:
                        pts = base_pts  # undetected
                    elif detected_brand_id == b["brand_id"]:
                        pts = int(base_pts * brand_mult)  # match
                    else:
                        pts = int(base_pts * brand_mult * 0.3)  # competitor deterrent
                else:
                    pts = int(base_pts * brand_mult)

                if pts > 0:
                    transaction_id += 1
                    desc_text = "recycling points"
                    if wt in CUP_TYPES and detected_brand_id == b["brand_id"]:
                        desc_text = "brand bonus"
                    elif wt in CUP_TYPES and detected_brand_id and detected_brand_id != b["brand_id"]:
                        desc_text = "competitor penalty"

                    transactions.append(
                        f"INSERT INTO recycling_transactions (id, user_id, detection_event_id, "
                        f"points_earned, type, description, created_at, updated_at) VALUES ("
                        f"{transaction_id}, {user_id_val}, {detection_id}, {pts}, 'earned', "
                        f"{sql_str(desc_text, dialect)}, "
                        f"{sql_ts(detected_at, dialect)}, {sql_ts(detected_at, dialect)});"
                    )
                    user_points[user_id_val] += pts

                    # Streak logic
                    last = user_last_recycled.get(user_id_val)
                    if last is None or (detected_at.date() - last).days >= 1:
                        if last and (detected_at.date() - last).days == 1:
                            user_streaks[user_id_val] += 1
                        elif last and (detected_at.date() - last).days > 1:
                            user_streaks[user_id_val] = 1
                        else:
                            user_streaks[user_id_val] = 1
                        user_last_recycled[user_id_val] = detected_at.date()
                        user_longest[user_id_val] = max(user_longest[user_id_val], user_streaks[user_id_val])

            # Pickup trigger at 80%
            if bin_fill[b["id"]] >= 80:
                pickup_id += 1
                collector = random.choice(collector_ids)
                claimed_at = detected_at + timedelta(minutes=random.randint(10, 120))
                completed_at = claimed_at + timedelta(minutes=random.randint(15, 90))
                pickups.append(
                    f"INSERT INTO pickup_requests (id, bin_id, status, claimed_by, claimed_at, "
                    f"completed_at, created_at, updated_at) VALUES ("
                    f"{pickup_id}, {b['id']}, 'completed', {collector}, "
                    f"{sql_ts(claimed_at, dialect)}, {sql_ts(completed_at, dialect)}, "
                    f"{sql_ts(detected_at, dialect)}, {sql_ts(completed_at, dialect)});"
                )
                bin_fill[b["id"]] = 0  # reset after pickup

        # Random report (~1 every 5 days)
        if random.random() < 0.2 and active_public and active_bins_at_date:
            report_id += 1
            ruser = random.choice(active_public)
            rbin = random.choice(active_bins_at_date)
            rtype = random.choice(REPORT_TYPES)
            rstatus = random.choices(REPORT_STATUSES, weights=[0.2, 0.1, 0.5, 0.2], k=1)[0]
            resolution = None
            resolved_by = None
            resolved_at = None
            if rstatus in ("resolved", "closed"):
                resolution = random.choice([
                    "Issue resolved. Bin serviced.", "Reported issue confirmed and fixed.",
                    "Detection model retrained.", "Bin replaced.", "False alarm - no action needed.",
                ])
                resolved_by = random.choice(admin_ids)
                resolved_at = current_date + timedelta(days=random.randint(1, 7))
            rtime = current_date.replace(hour=random.randint(8, 20), minute=random.randint(0, 59))
            reports.append(
                f"INSERT INTO reports (id, user_id, bin_id, type, description, status, "
                f"resolution, resolved_by, resolved_at, created_at, updated_at) VALUES ("
                f"{report_id}, {ruser}, {rbin['id']}, {sql_str(rtype, dialect)}, "
                f"{sql_str('Issue with bin ' + rbin['serial_number'] + ': ' + rtype.replace('_', ' '), dialect)}, "
                f"{sql_str(rstatus, dialect)}, {sql_str(resolution, dialect)}, "
                f"{resolved_by if resolved_by else 'NULL'}, "
                f"{sql_ts(resolved_at, dialect)}, "
                f"{sql_ts(rtime, dialect)}, {sql_ts(rtime, dialect)});"
            )

        current_date += timedelta(days=1)

    # Write detections
    for line in detections_batch:
        lines.append(line)
    lines.append("")

    # Write transactions
    lines.append("-- Recycling transactions")
    for line in transactions:
        lines.append(line)
    lines.append("")

    # Write pickups
    lines.append("-- Pickup requests")
    for line in pickups:
        lines.append(line)
    lines.append("")

    # ─── 8. REDEMPTIONS ──────────────────────────────────────────────
    lines.append("-- Redemptions")
    # Generate ~2000 redemptions from active users with points
    sorted_users = sorted(user_points.items(), key=lambda x: -x[1])
    active_redeemers = [uid for uid, pts in sorted_users if pts > 100][:50]

    for _ in range(min(2000, len(active_redeemers) * 40)):
        uid = random.choice(active_redeemers)
        reward = random.choice(rewards_data)
        brand_id_r, rname, rdesc, cost, _ = reward
        if user_points[uid] >= cost:
            redemption_id += 1
            user_points[uid] -= cost
            voucher = f"MBX-{''.join(random.choices(string.ascii_uppercase + string.digits, k=8))}"
            redeemed_at = datetime(2024, 1, 1) + timedelta(days=random.randint(30, 1090))
            expires_at = redeemed_at + timedelta(days=30)
            status = random.choices(["pending", "fulfilled", "expired"], weights=[0.1, 0.7, 0.2], k=1)[0]
            lines.append(
                f"INSERT INTO redemptions (id, user_id, reward_id, points_spent, voucher_code, "
                f"status, redeemed_at, expires_at, created_at, updated_at) VALUES ("
                f"{redemption_id}, {uid}, {rewards_data.index(reward) + 1}, {cost}, "
                f"{sql_str(voucher, dialect)}, {sql_str(status, dialect)}, "
                f"{sql_ts(redeemed_at, dialect)}, {sql_ts(expires_at, dialect)}, "
                f"{sql_ts(redeemed_at, dialect)}, {sql_ts(redeemed_at, dialect)});"
            )

            # Corresponding spent transaction
            transaction_id += 1
            lines.append(
                f"INSERT INTO recycling_transactions (id, user_id, detection_event_id, "
                f"points_earned, type, description, created_at, updated_at) VALUES ("
                f"{transaction_id}, {uid}, NULL, -{cost}, 'spent', "
                f"{sql_str(f'Redeemed: {rname}', dialect)}, "
                f"{sql_ts(redeemed_at, dialect)}, {sql_ts(redeemed_at, dialect)});"
            )
    lines.append("")

    # ─── 9. REPORTS ──────────────────────────────────────────────────
    lines.append("-- Reports")
    for line in reports:
        lines.append(line)
    lines.append("")

    # ─── 10. NOTIFICATIONS (sample) ──────────────────────────────────
    lines.append("-- App notifications (sample of recent)")
    notif_types = ["points_earned", "streak_milestone", "welcome", "system"]
    for uid in public_ids[:30]:
        for _ in range(random.randint(3, 15)):
            notification_id += 1
            ntype = random.choices(notif_types, weights=[0.6, 0.2, 0.05, 0.15], k=1)[0]
            title = {"points_earned": "Points Earned!", "streak_milestone": "Streak Milestone!",
                     "welcome": "Welcome to Mobius!", "system": "System Update"}[ntype]
            body = {"points_earned": f"You earned {random.randint(3,22)} points for recycling.",
                    "streak_milestone": f"You hit a {random.choice([7,14,30])}-day streak!",
                    "welcome": "Start recycling to earn points and rewards.",
                    "system": "New features available. Check out rewards!"}[ntype]
            nat = datetime(2024, 6, 1) + timedelta(days=random.randint(0, 900))
            read_at = nat + timedelta(hours=random.randint(1, 48)) if random.random() < 0.7 else None
            lines.append(
                f"INSERT INTO app_notifications (id, user_id, type, title, body, "
                f"read_at, created_at, updated_at) VALUES ("
                f"{notification_id}, {uid}, {sql_str(ntype, dialect)}, "
                f"{sql_str(title, dialect)}, {sql_str(body, dialect)}, "
                f"{sql_ts(read_at, dialect)}, {sql_ts(nat, dialect)}, {sql_ts(nat, dialect)});"
            )
    lines.append("")

    # ─── 11. UPDATE USER POINTS & STREAKS ────────────────────────────
    lines.append("-- Update user points and streaks")
    for uid in public_ids:
        pts = max(0, user_points.get(uid, 0))
        streak = user_streaks.get(uid, 0)
        longest = user_longest.get(uid, 0)
        last_r = user_last_recycled.get(uid)
        last_sql = sql_ts(datetime.combine(last_r, datetime.min.time()), dialect) if last_r else "NULL"
        lines.append(
            f"UPDATE users SET points_balance = {pts}, current_streak = {streak}, "
            f"longest_streak = {longest}, last_recycled_at = {last_sql} WHERE id = {uid};"
        )
    lines.append("")

    # ─── 12. UPDATE BIN FILL LEVELS ──────────────────────────────────
    lines.append("-- Set final bin fill levels")
    for b in bins:
        fl = bin_fill[b["id"]]
        lines.append(f"UPDATE bins SET fill_level = {fl} WHERE id = {b['id']};")
    lines.append("")

    # Re-enable FK checks
    if dialect == "sqlite":
        lines.append("PRAGMA foreign_keys = ON;")
    elif dialect == "mysql":
        lines.append("SET FOREIGN_KEY_CHECKS = 1;")

    # ─── Summary ─────────────────────────────────────────────────────
    lines.append("")
    lines.append(f"-- Summary:")
    lines.append(f"--   Brands: {len(BRANDS)}")
    lines.append(f"--   Users: {len(users)}")
    lines.append(f"--   Outlets: {len(outlets)}")
    lines.append(f"--   Bins: {len(bins)}")
    lines.append(f"--   Detections: {detection_id}")
    lines.append(f"--   Transactions: {transaction_id}")
    lines.append(f"--   Pickups: {pickup_id}")
    lines.append(f"--   Redemptions: {redemption_id}")
    lines.append(f"--   Reports: {report_id}")
    lines.append(f"--   Notifications: {notification_id}")
    lines.append(f"--   Rewards: {reward_id}")

    output = "\n".join(lines)

    if output_path:
        with open(output_path, "w") as f:
            f.write(output)
        print(f"Seed data written to {output_path}")
        print(f"  Brands: {len(BRANDS)}")
        print(f"  Users: {len(users)}")
        print(f"  Outlets: {len(outlets)}")
        print(f"  Bins: {len(bins)}")
        print(f"  Detections: {detection_id}")
        print(f"  Transactions: {transaction_id}")
        print(f"  Pickups: {pickup_id}")
        print(f"  Redemptions: {redemption_id}")
        print(f"  Reports: {report_id}")
        print(f"  Notifications: {notification_id}")
    else:
        print(output)


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Generate Mobius seed data")
    parser.add_argument("--dialect", choices=["sqlite", "mysql"], default="sqlite",
                        help="SQL dialect (default: sqlite)")
    parser.add_argument("--output", "-o", default=None,
                        help="Output file path (default: stdout)")
    args = parser.parse_args()
    generate(dialect=args.dialect, output_path=args.output)
