#!/usr/bin/env python3
"""
Generate a synthetic dataset of 1000+ rows for a college teacher feedback
and survey management system.  Includes all row types:
  USER, QUESTION, SURVEY, REVIEW, COMPLAINT, SUGGESTION

Output: synthetic_dataset.json + synthetic_dataset.csv
"""

import json, csv, random
from datetime import date, timedelta

# ── Realistic Indian name pools ──────────────────────────────────────────────
FIRST_NAMES_M = [
    "Aarav", "Vivaan", "Aditya", "Vihaan", "Arjun", "Reyansh", "Mohammed",
    "Sai", "Arnav", "Dhruv", "Kabir", "Ritvik", "Sahil", "Karan", "Rohan",
    "Ishaan", "Pranav", "Kunal", "Harsh", "Yash", "Tanmay", "Dev", "Manav",
    "Parth", "Rishi", "Siddharth", "Aakash", "Nikhil", "Rahul", "Abhishek",
    "Vikram", "Ankit", "Gaurav", "Varun", "Shubham", "Prateek", "Neeraj",
    "Raj", "Suraj", "Deepak", "Amit", "Sumit", "Mohit", "Ajay", "Vijay",
    "Mayank", "Tushar", "Chirag", "Hemant", "Piyush",
]
FIRST_NAMES_F = [
    "Ananya", "Diya", "Myra", "Sara", "Aanya", "Aadhya", "Isha", "Kiara",
    "Riya", "Priya", "Sneha", "Pooja", "Neha", "Kavya", "Meera",
    "Tanya", "Shruti", "Aishwarya", "Divya", "Swati", "Nandini", "Rashmi",
    "Pallavi", "Sanya", "Simran", "Jyoti", "Komal", "Sakshi", "Bhavna",
    "Garima", "Megha", "Ankita", "Tanvi", "Aditi", "Preeti", "Mansi",
    "Sonali", "Kriti", "Mitali", "Richa", "Parul", "Shivani", "Payal",
    "Harshita", "Rupal", "Nisha", "Vidhi", "Shalini", "Madhuri", "Aparna",
]
LAST_NAMES = [
    "Sharma", "Verma", "Gupta", "Singh", "Kumar", "Patel", "Joshi",
    "Mehta", "Reddy", "Nair", "Iyer", "Chauhan", "Yadav", "Mishra",
    "Pandey", "Deshmukh", "Kulkarni", "Jain", "Shah", "Bhat", "Pillai",
    "Menon", "Rao", "Das", "Banerjee", "Sen", "Bose", "Dutta", "Ghosh",
    "Chatterjee", "Roy", "Kaur", "Gill", "Arora", "Kapoor", "Malhotra",
    "Khanna", "Bajaj", "Saxena", "Tiwari", "Dubey", "Srivastava",
    "Chowdhury", "Mukherjee", "Thakur", "Patil", "Shinde", "More",
    "Pawar", "Jadhav",
]

COURSES = ["B.Tech CS", "B.Tech IT", "B.Tech Electronics", "B.Tech EXTC",
           "B.Tech Mechanical", "B.Tech Civil"]

DIVISIONS = ["CS-A", "CS-B", "IT-A", "IT-B", "EXTC-A", "EXTC-B",
             "MECH-A", "MECH-B", "CIVIL-A"]

DEPARTMENTS = ["Computer Science", "Information Technology", "Electronics",
               "EXTC", "Mechanical Engineering", "Civil Engineering",
               "Mathematics", "Physics"]

SUBJECT_POOL = {
    "Computer Science": ["Programming", "Data Structures", "Algorithms",
                         "DBMS", "Operating Systems", "Computer Networks",
                         "Machine Learning", "Web Development", "Software Engineering"],
    "Information Technology": ["Programming", "Web Development", "DBMS",
                               "Cloud Computing", "Cyber Security", "Data Analytics",
                               "Software Testing"],
    "Electronics": ["Digital Electronics", "Analog Circuits", "VLSI Design",
                    "Embedded Systems", "Signal Processing", "Control Systems"],
    "EXTC": ["Communication Systems", "Microwave Engineering", "Antenna Design",
             "Signal Processing", "Digital Electronics", "IoT"],
    "Mechanical Engineering": ["Thermodynamics", "Fluid Mechanics",
                                "Manufacturing", "Machine Design", "CAD/CAM",
                                "Heat Transfer"],
    "Civil Engineering": ["Structural Analysis", "Surveying", "Concrete Technology",
                          "Geotechnical Engineering", "Transportation Engineering"],
    "Mathematics": ["Calculus", "Linear Algebra", "Probability & Statistics",
                    "Discrete Mathematics", "Numerical Methods"],
    "Physics": ["Engineering Physics", "Quantum Mechanics", "Optics",
                "Electromagnetism"],
}

COLLEGE_DOMAIN = "college.edu"

# ── Question templates (for QUESTION rows) ───────────────────────────────────
STUDENT_QUESTIONS = [
    ("Teaching effectiveness", "How effective is the teacher in delivering lectures and ensuring student understanding?"),
    ("Clarity of explanation", "How clearly does the teacher explain concepts and topics?"),
    ("Availability for doubts", "How available is the teacher for resolving student doubts outside of class?"),
    ("Course organization", "How well-organized is the course structure, including syllabus and schedule?"),
    ("Interaction with students", "How well does the teacher interact with and engage students during class?"),
    ("Punctuality", "How punctual is the teacher in attending and conducting classes?"),
    ("Use of teaching aids", "How effectively does the teacher use visual aids, slides, and digital tools?"),
    ("Fairness in grading", "How fair and transparent is the teacher in evaluating and grading assignments?"),
    ("Encouraging participation", "How well does the teacher encourage active participation and discussion?"),
    ("Knowledge of subject", "How well-versed is the teacher in the subject matter?"),
    ("Assignment relevance", "How relevant and helpful are the assignments given by the teacher?"),
    ("Feedback quality", "How constructive and timely is the feedback provided by the teacher?"),
    ("Practical application", "How well does the teacher relate theory to practical real-world applications?"),
    ("Communication skills", "How effective are the teacher's overall communication skills?"),
    ("Lab guidance", "How well does the teacher guide students during practical/lab sessions?"),
]

TEACHER_QUESTIONS = [
    ("Administrative support", "How satisfied are you with the administrative support provided?"),
    ("Resource availability", "How adequate are the teaching resources and materials available?"),
    ("Workload fairness", "How fair is the distribution of workload among faculty?"),
    ("Professional growth", "How satisfied are you with professional development opportunities?"),
    ("Infrastructure quality", "How would you rate the quality of classroom and lab infrastructure?"),
]

# ── Realistic comment templates ──────────────────────────────────────────────
POSITIVE_COMMENTS = [
    "Excellent teaching style, really enjoyed the lectures.",
    "Very clear explanations, easy to follow along.",
    "Always available to help with doubts, very approachable.",
    "One of the best teachers I've had in college.",
    "Makes difficult concepts seem simple.",
    "Lectures are well-organized and engaging.",
    "Great use of real-world examples to explain theory.",
    "Very patient and supportive teacher.",
    "I learned a lot from this course, highly recommend.",
    "Amazing practical sessions, very hands-on approach.",
    "The teacher is very passionate about the subject.",
    "Provides excellent study material and references.",
    "Encourages students to think critically and ask questions.",
    "Very fair in grading and provides detailed feedback.",
    "Classes are always interesting and never boring.",
    "The best teacher in our department.",
    "Makes the subject really interesting to study.",
    "Always well-prepared for lectures.",
    "Very knowledgeable and experienced.",
    "Great mentor, helped me understand my career goals.",
]

NEUTRAL_COMMENTS = [
    "The teaching is okay, could be improved.",
    "Average experience, nothing exceptional.",
    "Some topics were explained well, others not so much.",
    "The lectures are informative but a bit monotonous.",
    "Decent teacher, does the job.",
    "Could use more practical examples.",
    "The pace of teaching is sometimes too fast.",
    "Overall satisfactory teaching.",
    "Good knowledge of subject but needs better delivery.",
    "Needs to use more modern teaching methods.",
    "The course content is good but presentation could improve.",
    "Sometimes hard to understand the explanations.",
    "More interactive sessions would be helpful.",
    "The teacher is knowledgeable but not very approachable.",
    "Assignments could be more relevant to the syllabus.",
]

NEGATIVE_COMMENTS = [
    "The teaching could be much better.",
    "Difficult to understand the explanations sometimes.",
    "Not very approachable for doubt solving.",
    "Needs to improve interaction with students.",
    "The course feels disorganized at times.",
    "Grading seems inconsistent and unfair sometimes.",
    "Not enough practical examples provided.",
    "Classes are often boring and hard to sit through.",
    "The teacher rushes through important topics.",
    "Needs to be more patient with students who struggle.",
]

# ── Review comment templates ─────────────────────────────────────────────────
REVIEW_COMMENTS = [
    "Excellent teacher! Highly recommend.",
    "Very helpful and patient with students.",
    "Great at explaining complex topics in simple terms.",
    "Could improve on being more punctual.",
    "Good teaching but needs more practical examples.",
    "One of the most knowledgeable teachers in the department.",
    "Always willing to stay after class to clear doubts.",
    "Very organized and follows a clear teaching plan.",
    "Needs to engage students more during lectures.",
    "Outstanding dedication to student success.",
    "Fair in grading but could provide more feedback.",
    "Makes learning fun with interactive examples.",
    "The teacher needs to slow down during complex topics.",
    "Very supportive and understanding.",
    "Good teacher overall, minor improvements needed.",
]

# ── Complaint templates ──────────────────────────────────────────────────────
COMPLAINT_SUBJECTS = [
    ("Broken AC", "The AC in room {room} is not working properly and classes are very uncomfortable."),
    ("Projector malfunction", "The projector in room {room} has been flickering and showing distorted images for weeks."),
    ("Internet connectivity", "Wi-Fi connectivity in {area} is extremely slow and unreliable during peak hours."),
    ("Cleanliness issue", "The washrooms near {area} are not being cleaned regularly and are unhygienic."),
    ("Broken furniture", "Several chairs and desks in room {room} are broken and need immediate replacement."),
    ("Water supply", "The water cooler near {area} has not been working for the past week."),
    ("Noisy environment", "Construction work near {area} is causing excessive noise during lecture hours."),
    ("Lab equipment", "Multiple computers in {area} are not functioning, affecting practical sessions."),
    ("Library issue", "Several important reference books in the library are missing or damaged."),
    ("Parking problem", "The parking area is overcrowded and there is no proper arrangement for student vehicles."),
]

# ── Suggestion templates ─────────────────────────────────────────────────────
SUGGESTION_SUBJECTS = [
    ("More Lab Hours", "Please extend lab hours during weekends for project work and practice."),
    ("Guest lectures", "Organizing guest lectures from industry professionals would greatly benefit students."),
    ("Online resources", "Please provide access to online learning platforms like Coursera or Udemy."),
    ("Study groups", "Facilitate formation of peer study groups for collaborative learning."),
    ("Career counseling", "Regular career counseling sessions would help students plan their futures."),
    ("Sports facilities", "Improve sports facilities and organize more inter-college tournaments."),
    ("Mental health support", "Introduce counseling services for students dealing with academic stress."),
    ("Hackathon events", "Organize regular hackathons and coding competitions on campus."),
    ("Industry visits", "Arrange industrial visits to give students real-world exposure."),
    ("Feedback system", "Implement an anonymous real-time feedback system for continuous improvement."),
    ("Cafeteria menu", "The cafeteria menu should include more healthy and diverse food options."),
    ("Exam schedule", "Distribute exams more evenly across the semester to reduce student stress."),
]

ROOMS = ["101", "102", "201", "202", "301", "302", "401", "402", "Lab 1", "Lab 2", "Lab 3"]
AREAS = ["Block A", "Block B", "Block C", "the Science Wing", "the Engineering Block",
         "the Computer Lab", "Building 2", "the Main Building"]


def random_email(first: str, last: str, suffix: str = "") -> str:
    sep = random.choice([".", "_", ""])
    num = random.randint(1, 99)
    return f"{first.lower()}{sep}{last.lower()}{num}{suffix}@{COLLEGE_DOMAIN}"


def random_dob(start_year: int, end_year: int) -> str:
    start = date(start_year, 1, 1)
    end = date(end_year, 12, 31)
    delta = (end - start).days
    return (start + timedelta(days=random.randint(0, delta))).isoformat()


def pick_name():
    gender = random.choice(["M", "F"])
    first = random.choice(FIRST_NAMES_M if gender == "M" else FIRST_NAMES_F)
    last = random.choice(LAST_NAMES)
    return first, last


# ═══════════════════════════════════════════════════════════════════════════
#  Generate rows
# ═══════════════════════════════════════════════════════════════════════════
rows: list[dict] = []
FIELDS = [
    "row_type", "username", "email", "role", "dob", "division", "roll_no",
    "course", "department", "subjects", "experience", "question_id",
    "rating", "teacher_email", "comment", "subject", "description", "status",
]

def new_row(**kw) -> dict:
    row = {f: None for f in FIELDS}
    row.update(kw)
    return row

# ── 1. USERS (200 rows) ─────────────────────────────────────────────────────
used_emails: set[str] = set()
student_emails: list[str] = []
teacher_records: list[dict] = []

NUM_STUDENTS = 150
NUM_TEACHERS = 40
NUM_ADMINS = 10

# Students
for i in range(NUM_STUDENTS):
    first, last = pick_name()
    uname = f"{first.lower()}_{last.lower()}{random.randint(1,99)}"
    email = random_email(first, last)
    while email in used_emails:
        email = random_email(first, last)
    used_emails.add(email)
    student_emails.append(email)

    course = random.choice(COURSES)
    prefix = course.split()[-1][:2].upper()
    matching_divs = [d for d in DIVISIONS if d.startswith(prefix)] or DIVISIONS
    division = random.choice(matching_divs)
    roll_no = f"{prefix}{random.randint(1,200):03d}"

    rows.append(new_row(
        row_type="USER", username=uname, email=email, role="student",
        dob=random_dob(2000, 2005), division=division, roll_no=roll_no,
        course=course,
    ))

# Teachers
for i in range(NUM_TEACHERS):
    first, last = pick_name()
    uname = f"prof_{first.lower()}_{last.lower()}"
    email = random_email(first, last, "_t")
    while email in used_emails:
        email = random_email(first, last, "_t")
    used_emails.add(email)

    dept = random.choice(DEPARTMENTS)
    subjs = random.sample(SUBJECT_POOL[dept], k=min(random.randint(2, 4), len(SUBJECT_POOL[dept])))
    exp = random.randint(2, 30)

    teacher_records.append({"email": email, "department": dept, "subjects": subjs})
    rows.append(new_row(
        row_type="USER", username=uname, email=email, role="teacher",
        department=dept, subjects=",".join(subjs), experience=exp,
    ))

# Admins
for i in range(NUM_ADMINS):
    first, last = pick_name()
    uname = f"admin_{first.lower()}{random.randint(1,9)}"
    email = f"admin{i+1}@{COLLEGE_DOMAIN}"
    while email in used_emails:
        email = f"admin{i+100}@{COLLEGE_DOMAIN}"
    used_emails.add(email)
    rows.append(new_row(
        row_type="USER", username=uname, email=email, role="admin",
    ))

# ── 2. QUESTIONS (20 rows) ──────────────────────────────────────────────────
for subject, description in STUDENT_QUESTIONS:
    rows.append(new_row(
        row_type="QUESTION", role="student",
        subject=subject, description=description, status="active",
    ))

for subject, description in TEACHER_QUESTIONS:
    rows.append(new_row(
        row_type="QUESTION", role="teacher",
        subject=subject, description=description, status="active",
    ))

# ── 3. SURVEY ROWS (600 rows) ───────────────────────────────────────────────
# Note: question_id references existing questions in DB (IDs 1-10 from seed data).
# The 20 QUESTION rows above will get new auto-increment IDs, so surveys reference
# the pre-seeded question IDs that already exist.
for _ in range(600):
    rating = random.choices([1, 2, 3, 4, 5], weights=[5, 10, 20, 35, 30])[0]
    if rating >= 4:
        comment = random.choice(POSITIVE_COMMENTS)
    elif rating == 3:
        comment = random.choice(NEUTRAL_COMMENTS)
    else:
        comment = random.choice(NEGATIVE_COMMENTS)

    rows.append(new_row(
        row_type="SURVEY",
        email=random.choice(student_emails),
        question_id=random.randint(1, 10),
        rating=rating,
        teacher_email=random.choice(teacher_records)["email"],
        comment=comment,
    ))

# ── 4. REVIEW ROWS (100 rows) ───────────────────────────────────────────────
for _ in range(100):
    rating = random.choices([1, 2, 3, 4, 5], weights=[3, 8, 15, 40, 34])[0]
    rows.append(new_row(
        row_type="REVIEW",
        email=random.choice(student_emails),
        rating=rating,
        teacher_email=random.choice(teacher_records)["email"],
        comment=random.choice(REVIEW_COMMENTS),
    ))

# ── 5. COMPLAINTS (40 rows) ─────────────────────────────────────────────────
for _ in range(40):
    template = random.choice(COMPLAINT_SUBJECTS)
    subject = template[0]
    description = template[1].format(
        room=random.choice(ROOMS),
        area=random.choice(AREAS),
    )
    status = random.choices(["pending", "in_progress", "resolved"], weights=[50, 30, 20])[0]

    rows.append(new_row(
        row_type="COMPLAINT",
        email=random.choice(student_emails),
        subject=subject, description=description, status=status,
    ))

# ── 6. SUGGESTIONS (40 rows) ────────────────────────────────────────────────
for _ in range(40):
    template = random.choice(SUGGESTION_SUBJECTS)
    subject = template[0]
    description = template[1]
    status = random.choices(["pending", "in_progress", "resolved"], weights=[60, 25, 15])[0]

    rows.append(new_row(
        row_type="SUGGESTION",
        email=random.choice(student_emails),
        subject=subject, description=description, status=status,
    ))

# ═══════════════════════════════════════════════════════════════════════════
#  Write outputs: JSON + CSV
# ═══════════════════════════════════════════════════════════════════════════
json_path = r"c:\xampp\htdocs\stu\synthetic_dataset.json"
csv_path  = r"c:\xampp\htdocs\stu\synthetic_dataset.csv"

with open(json_path, "w", encoding="utf-8") as f:
    json.dump(rows, f, indent=2, ensure_ascii=False)

with open(csv_path, "w", newline="", encoding="utf-8") as f:
    w = csv.DictWriter(f, fieldnames=FIELDS)
    w.writeheader()
    w.writerows(rows)

# Print summary
total = len(rows)
counts = {}
for r in rows:
    t = r["row_type"]
    counts[t] = counts.get(t, 0) + 1

print(f"Generated {total} rows -> JSON + CSV")
for t in ["USER", "QUESTION", "SURVEY", "REVIEW", "COMPLAINT", "SUGGESTION"]:
    print(f"  {t:12s}: {counts.get(t, 0)}")
