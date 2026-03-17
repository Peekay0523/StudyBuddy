-- SEO Pages Migration - 51 Long-tail Keywords
-- Career guidance and course selection pages for South African students
-- Run this to populate the seo_pages table with career guidance content

-- Insert all 51 SEO pages
INSERT OR IGNORE INTO seo_pages (
    title, slug, meta_title, meta_description, content_type, 
    subject, grade_level, topic, search_intent, target_keyword, 
    status, full_content, schema_markup
) VALUES

-- 1. How to choose the right course after matric in South Africa
(
    'How to Choose the Right Course After Matric in South Africa',
    'how-to-choose-right-course-after-matric-south-africa',
    'How to Choose the Right Course After Matric | Career Guide 2026',
    'Complete guide to choosing the right course after matric in South Africa. Learn about APS requirements, career paths, and university applications.',
    'static',
    'Career Guidance',
    'Grade 12',
    'Course Selection',
    'informational',
    'how to choose the right course after matric',
    'published',
    '<h2>Introduction</h2><p>Choosing the right course after matric is one of the most important decisions you will make in your life. This comprehensive guide will help South African students navigate the complex process of selecting a career path that matches their interests, skills, and academic achievements.</p><h2>Understanding Your APS Score</h2><p>Your Admission Point Score (APS) is calculated from your six matric subjects and determines which courses you qualify for. Different institutions and courses have different APS requirements.</p><h2>Steps to Choose the Right Course</h2><ol><li>Assess your interests and passions</li><li>Review your academic strengths</li><li>Research career opportunities</li><li>Check APS requirements</li><li>Consider job market demand</li><li>Evaluate financial implications</li></ol><h2>Career Fields in High Demand</h2><p>South Africa has high demand for professionals in: Technology, Healthcare, Engineering, Finance, Education, and Renewable Energy sectors.</p>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"Career Guidance - Course Selection","educationalLevel":"Post-Secondary","description":"Guide for choosing courses after matric"}'
),

-- 2-10. APS-specific course selection pages (20-28)
(
    'How to Choose the Right Course After Matric with APS of 20',
    'how-to-choose-right-course-after-matric-aps-20',
    'Courses You Can Study with APS 20 | South Africa 2026',
    'Discover what courses and careers are available with an APS score of 20. Complete guide for South African students.',
    'static', 'Career Guidance', 'Grade 12', 'APS 20 Courses', 'informational',
    'courses with APS 20 South Africa', 'published',
    '<h2>Courses Available with APS 20</h2><p>An APS of 20 opens several doors for South African students. You can pursue certificates, diplomas, and some degree programs.</p><h3>Certificate Programs</h3><ul><li>Business Administration</li><li>Information Technology Support</li><li>Tourism Management</li><li>Early Childhood Development</li></ul><h3>Diploma Programs</h3><ul><li>Marketing Management</li><li>Human Resource Management</li><li>Financial Accounting</li><li>Office Administration</li></ul><h3>Degree Programs</h3><ul><li>General Studies (with bridging courses)</li><li>Arts and Humanities</li></ul>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"APS 20 Career Guide"}'
),

(
    'How to Choose the Right Course After Matric with APS of 21',
    'how-to-choose-right-course-after-matric-aps-21',
    'Courses You Can Study with APS 21 | South Africa 2026',
    'Find the best courses and career paths available with an APS score of 21 in South Africa.',
    'static', 'Career Guidance', 'Grade 12', 'APS 21 Courses', 'informational',
    'courses with APS 21 South Africa', 'published',
    '<h2>Courses Available with APS 21</h2><p>With an APS of 21, you have expanded options including many diploma and some bachelor degree programs.</p><h3>Recommended Courses</h3><ul><li>Business Management</li><li>Marketing</li><li>Public Relations</li><li>Journalism</li><li>Graphic Design</li><li>IT Networking</li></ul>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"APS 21 Career Guide"}'
),

(
    'How to Choose the Right Course After Matric with APS of 22',
    'how-to-choose-right-course-after-matric-aps-22',
    'Courses You Can Study with APS 22 | South Africa 2026',
    'Explore career opportunities and courses available with an APS score of 22.',
    'static', 'Career Guidance', 'Grade 12', 'APS 22 Courses', 'informational',
    'courses with APS 22 South Africa', 'published',
    '<h2>Courses Available with APS 22</h2><p>An APS of 22 qualifies you for many undergraduate programs at universities and universities of technology.</p><h3>Popular Choices</h3><ul><li>Commerce (General)</li><li>Education Foundation Phase</li><li>Social Work</li><li>Media Studies</li><li>Law (Extended Programs)</li></ul>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"APS 22 Career Guide"}'
),

(
    'How to Choose the Right Course After Matric with APS of 23',
    'how-to-choose-right-course-after-matric-aps-23',
    'Courses You Can Study with APS 23 | South Africa 2026',
    'Discover degree programs and career paths for students with APS 23.',
    'static', 'Career Guidance', 'Grade 12', 'APS 23 Courses', 'informational',
    'courses with APS 23 South Africa', 'published',
    '<h2>Courses Available with APS 23</h2><p>With 23 APS points, you have access to many mainstream bachelor degree programs.</p><h3>Degree Options</h3><ul><li>Business Commerce</li><li>Arts and Social Sciences</li><li>Education</li><li>Law (Extended)</li><li>Health Sciences (Extended)</li></ul>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"APS 23 Career Guide"}'
),

(
    'How to Choose the Right Course After Matric with APS of 24',
    'how-to-choose-right-course-after-matric-aps-24',
    'Courses You Can Study with APS 24 | South Africa 2026',
    'Find the best degree programs available with an APS score of 24.',
    'static', 'Career Guidance', 'Grade 12', 'APS 24 Courses', 'informational',
    'courses with APS 24 South Africa', 'published',
    '<h2>Courses Available with APS 24</h2><p>APS 24 opens doors to most mainstream undergraduate degrees.</p><h3>Recommended Programs</h3><ul><li>Commerce and Management</li><li>Humanities</li><li>Education</li><li>Law</li><li>Nursing</li></ul>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"APS 24 Career Guide"}'
),

(
    'How to Choose the Right Course After Matric with APS of 25',
    'how-to-choose-right-course-after-matric-aps-25',
    'Courses You Can Study with APS 25 | South Africa 2026',
    'Explore excellent career opportunities with an APS score of 25.',
    'static', 'Career Guidance', 'Grade 12', 'APS 25 Courses', 'informational',
    'courses with APS 25 South Africa', 'published',
    '<h2>Courses Available with APS 25</h2><p>With 25 points, you qualify for most undergraduate programs including competitive fields.</p><h3>Top Choices</h3><ul><li>Commerce</li><li>Engineering (Extended)</li><li>Health Sciences</li><li>Law</li><li>Psychology</li></ul>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"APS 25 Career Guide"}'
),

(
    'How to Choose the Right Course After Matric with APS of 26',
    'how-to-choose-right-course-after-matric-aps-26',
    'Courses You Can Study with APS 26 | South Africa 2026',
    'Discover premium degree programs available with an APS score of 26.',
    'static', 'Career Guidance', 'Grade 12', 'APS 26 Courses', 'informational',
    'courses with APS 26 South Africa', 'published',
    '<h2>Courses Available with APS 26</h2><p>APS 26 qualifies you for highly competitive programs.</p><h3>Premium Programs</h3><ul><li>Engineering</li><li>Commerce</li><li>Health Sciences</li><li>Law</li><li>Actuarial Science (Extended)</li></ul>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"APS 26 Career Guide"}'
),

(
    'How to Choose the Right Course After Matric with APS of 27',
    'how-to-choose-right-course-after-matric-aps-27',
    'Courses You Can Study with APS 27 | South Africa 2026',
    'Access top-tier degree programs with an APS score of 27.',
    'static', 'Career Guidance', 'Grade 12', 'APS 27 Courses', 'informational',
    'courses with APS 27 South Africa', 'published',
    '<h2>Courses Available with APS 27</h2><p>With 27 points, you have excellent options across all faculties.</p><h3>Top Programs</h3><ul><li>Engineering</li><li>Medicine (Extended)</li><li>Pharmacy</li><li>Commerce</li><li>Law</li></ul>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"APS 27 Career Guide"}'
),

-- 9-10. APS 28-29
(
    'How to Choose the Right Course After Matric with APS of 28',
    'how-to-choose-right-course-after-matric-aps-28',
    'Courses You Can Study with APS 28 | South Africa 2026',
    'Elite degree programs available with an APS score of 28.',
    'static', 'Career Guidance', 'Grade 12', 'APS 28 Courses', 'informational',
    'courses with APS 28 South Africa', 'published',
    '<h2>Courses Available with APS 28</h2><p>APS 28 places you in the top tier of applicants.</p><h3>Elite Programs</h3><ul><li>Medicine (MBChB)</li><li>Engineering</li><li>Actuarial Science</li><li>Commerce</li><li>Law</li></ul>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"APS 28 Career Guide"}'
),

(
    'How to Choose the Right Course After Matric with APS of 29',
    'how-to-choose-right-course-after-matric-aps-29',
    'Courses You Can Study with APS 29 | South Africa 2026',
    'Maximum options with APS 29 - all degree programs available.',
    'static', 'Career Guidance', 'Grade 12', 'APS 29 Courses', 'informational',
    'courses with APS 29 South Africa', 'published',
    '<h2>Courses Available with APS 29</h2><p>With 29+ points, you qualify for ALL undergraduate programs including the most competitive.</p><h3>All Programs Open</h3><ul><li>Medicine (MBChB)</li><li>Dentistry</li><li>Engineering</li><li>Actuarial Science</li><li>Law</li><li>Commerce</li></ul>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"APS 29 Career Guide"}'
),

-- 11-22. "What course should I study" series
(
    'What Course Should I Study After Matric',
    'what-course-should-i-study-after-matric',
    'What Course Should I Study After Matric? | Career Quiz & Guide',
    'Not sure what to study after matric? Take our career quiz and discover the perfect course for your interests and skills.',
    'static', 'Career Guidance', 'Grade 12', 'Career Selection', 'informational',
    'what course should i study after matric', 'published',
    '<h2>Finding Your Perfect Course</h2><p>Choosing what to study after matric requires self-reflection and research. Consider: Your interests, Your strengths, Job market demand, Salary expectations, Study duration.</p><h2>Career Assessment Questions</h2><ul><li>What subjects do you enjoy most?</li><li>What are your hobbies and interests?</li><li>Do you prefer working with people, data, or things?</li><li>What is your ideal work environment?</li></ul>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"Career Selection Guide"}'
),

(
    'What Course Should I Study After Matric with APS of 20',
    'what-course-should-i-study-after-matric-aps-20',
    'Best Courses with APS 20 | Career Recommendations',
    'Find the best courses to study with an APS score of 20. Career recommendations and university options.',
    'static', 'Career Guidance', 'Grade 12', 'APS 20', 'informational',
    'what course should i study with APS 20', 'published',
    '<h2>Best Courses for APS 20</h2><p>With 20 points, consider: Business certificates, IT support courses, Tourism and hospitality, Education assistant programs.</p>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"APS 20 Recommendations"}'
),

(
    'What Course Should I Study After Matric with APS of 21',
    'what-course-should-i-study-after-matric-aps-21',
    'Best Courses with APS 21 | Career Recommendations',
    'Discover ideal courses for your APS 21 score with our career guide.',
    'static', 'Career Guidance', 'Grade 12', 'APS 21', 'informational',
    'what course should i study with APS 21', 'published',
    '<h2>Best Courses for APS 21</h2><p>Consider: Marketing diplomas, Business management, HR management, Media studies.</p>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"APS 21 Recommendations"}'
),

(
    'What Course Should I Study After Matric with APS of 22',
    'what-course-should-i-study-after-matric-aps-22',
    'Best Courses with APS 22 | Career Recommendations',
    'Explore the best career paths and courses for APS 22 students.',
    'static', 'Career Guidance', 'Grade 12', 'APS 22', 'informational',
    'what course should i study with APS 22', 'published',
    '<h2>Best Courses for APS 22</h2><p>Great options: Commerce degrees, Education, Social sciences, Law extended programs.</p>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"APS 22 Recommendations"}'
),

(
    'What Course Should I Study After Matric with APS of 23',
    'what-course-should-i-study-after-matric-aps-23',
    'Best Courses with APS 23 | Career Recommendations',
    'Find your ideal course with an APS score of 23.',
    'static', 'Career Guidance', 'Grade 12', 'APS 23', 'informational',
    'what course should i study with APS 23', 'published',
    '<h2>Best Courses for APS 23</h2><p>Recommended: Business commerce, Arts, Education, Health sciences extended.</p>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"APS 23 Recommendations"}'
),

(
    'What Course Should I Study After Matric with APS of 24',
    'what-course-should-i-study-after-matric-aps-24',
    'Best Courses with APS 24 | Career Recommendations',
    'Discover excellent course options with APS 24.',
    'static', 'Career Guidance', 'Grade 12', 'APS 24', 'informational',
    'what course should i study with APS 24', 'published',
    '<h2>Best Courses for APS 24</h2><p>Top choices: Commerce, Humanities, Education, Nursing, Law.</p>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"APS 24 Recommendations"}'
),

(
    'What Course Should I Study After Matric with APS of 25',
    'what-course-should-i-study-after-matric-aps-25',
    'Best Courses with APS 25 | Career Recommendations',
    'Premium course recommendations for students with APS 25.',
    'static', 'Career Guidance', 'Grade 12', 'APS 25', 'informational',
    'what course should i study with APS 25', 'published',
    '<h2>Best Courses for APS 25</h2><p>Excellent options: Commerce, Engineering extended, Health sciences, Psychology.</p>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"APS 25 Recommendations"}'
),

(
    'What Course Should I Study After Matric with APS of 26',
    'what-course-should-i-study-after-matric-aps-26',
    'Best Courses with APS 26 | Career Recommendations',
    'High-value courses available with APS 26.',
    'static', 'Career Guidance', 'Grade 12', 'APS 26', 'informational',
    'what course should i study with APS 26', 'published',
    '<h2>Best Courses for APS 26</h2><p>Premium programs: Engineering, Health sciences, Commerce, Law.</p>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"APS 26 Recommendations"}'
),

(
    'What Course Should I Study After Matric with APS of 27',
    'what-course-should-i-study-after-matric-aps-27',
    'Best Courses with APS 27 | Career Recommendations',
    'Top-tier degree programs for APS 27 students.',
    'static', 'Career Guidance', 'Grade 12', 'APS 27', 'informational',
    'what course should i study with APS 27', 'published',
    '<h2>Best Courses for APS 27</h2><p>Elite options: Medicine extended, Engineering, Pharmacy, Actuarial science.</p>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"APS 27 Recommendations"}'
),

(
    'What Course Should I Study After Matric with APS of 28',
    'what-course-should-i-study-after-matric-aps-28',
    'Best Courses with APS 28 | Career Recommendations',
    'Elite programs available with APS 28.',
    'static', 'Career Guidance', 'Grade 12', 'APS 28', 'informational',
    'what course should i study with APS 28', 'published',
    '<h2>Best Courses for APS 28</h2><p>All programs open: Medicine, Dentistry, Engineering, Actuarial science.</p>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"APS 28 Recommendations"}'
),

(
    'What Course Should I Study After Matric with APS of 29',
    'what-course-should-i-study-after-matric-aps-29',
    'Best Courses with APS 29 | Career Recommendations',
    'Maximum options - all courses available with APS 29.',
    'static', 'Career Guidance', 'Grade 12', 'APS 29', 'informational',
    'what course should i study with APS 29', 'published',
    '<h2>Best Courses for APS 29</h2><p>Every program is available: Medicine, Dentistry, Engineering, Law, Commerce, Actuarial Science.</p>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"APS 29 Recommendations"}'
),

-- 23-33. Subject-specific course pages
(
    'Courses I Can Study in South Africa When I Did History',
    'courses-i-can-study-south-africa-history',
    'Courses for History Students | Career Options South Africa',
    'Did History in matric? Discover all the courses and careers available to you in South Africa.',
    'static', 'Career Guidance', 'Grade 12', 'History Careers', 'informational',
    'courses for history students south africa', 'published',
    '<h2>Careers with History</h2><p>History opens doors to: Archaeology, Museum Studies, Heritage Management, Teaching, Law, Journalism, Political Science, International Relations, Tourism.</p>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"History Career Guide"}'
),

(
    'Courses I Can Study in South Africa When I Did Maths Lit',
    'courses-i-can-study-south-africa-maths-lit',
    'Courses for Mathematical Literacy Students | Career Options',
    'Did Maths Lit? Explore all available courses and career paths in South Africa.',
    'static', 'Career Guidance', 'Grade 12', 'Maths Lit Careers', 'informational',
    'courses for maths lit students south africa', 'published',
    '<h2>Careers with Mathematical Literacy</h2><p>Options include: Business Management, Marketing, Human Resources, Tourism, Hospitality, Retail Management, Administration, Early Childhood Development.</p>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"Maths Lit Career Guide"}'
),

(
    'Courses I Can Study in South Africa When I Did Geography',
    'courses-i-can-study-south-africa-geography',
    'Courses for Geography Students | Career Options South Africa',
    'Did Geography? Discover exciting career paths available to you.',
    'static', 'Career Guidance', 'Grade 12', 'Geography Careers', 'informational',
    'courses for geography students south africa', 'published',
    '<h2>Careers with Geography</h2><p>Geography leads to: Environmental Science, Urban Planning, GIS Specialist, Meteorology, Tourism Management, Teaching, Geology.</p>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"Geography Career Guide"}'
),

(
    'Courses I Can Study in South Africa When I Did Life Science',
    'courses-i-can-study-south-africa-life-science',
    'Courses for Life Science Students | Career Options South Africa',
    'Did Life Science? Explore healthcare, biology, and science careers.',
    'static', 'Career Guidance', 'Grade 12', 'Life Science Careers', 'informational',
    'courses for life science students south africa', 'published',
    '<h2>Careers with Life Science</h2><p>Life Science opens: Medicine, Nursing, Pharmacy, Physiotherapy, Biotechnology, Genetics, Ecology, Teaching, Veterinary Science.</p>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"Life Science Career Guide"}'
),

(
    'Courses I Can Study in South Africa When I Did Physics',
    'courses-i-can-study-south-africa-physics',
    'Courses for Physical Sciences Students | Career Options',
    'Did Physical Sciences/Physics? Discover engineering and science careers.',
    'static', 'Career Guidance', 'Grade 12', 'Physics Careers', 'informational',
    'courses for physics students south africa', 'published',
    '<h2>Careers with Physical Sciences</h2><p>Physics qualifies you for: Engineering (all fields), Physics, Astronomy, Data Science, Teaching, Research, Aviation.</p>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"Physics Career Guide"}'
),

(
    'Courses I Can Study in South Africa When I Did Business',
    'courses-i-can-study-south-africa-business',
    'Courses for Business Studies Students | Career Options',
    'Did Business Studies? Explore commerce and business career paths.',
    'static', 'Career Guidance', 'Grade 12', 'Business Careers', 'informational',
    'courses for business students south africa', 'published',
    '<h2>Careers with Business Studies</h2><p>Business Studies leads to: Business Management, Entrepreneurship, Marketing, Finance, Human Resources, Supply Chain Management.</p>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"Business Career Guide"}'
),

(
    'Courses I Can Study in South Africa When I Did Economics',
    'courses-i-can-study-south-africa-economics',
    'Courses for Economics Students | Career Options South Africa',
    'Did Economics? Discover finance, business, and economics careers.',
    'static', 'Career Guidance', 'Grade 12', 'Economics Careers', 'informational',
    'courses for economics students south africa', 'published',
    '<h2>Careers with Economics</h2><p>Economics opens: Economics, Finance, Actuarial Science, Business Commerce, Banking, Policy Analysis, Data Analytics.</p>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"Economics Career Guide"}'
),

(
    'Courses I Can Study in South Africa When I Did Accounting',
    'courses-i-can-study-south-africa-accounting',
    'Courses for Accounting Students | Career Options South Africa',
    'Did Accounting? Explore accounting, finance, and business careers.',
    'static', 'Career Guidance', 'Grade 12', 'Accounting Careers', 'informational',
    'courses for accounting students south africa', 'published',
    '<h2>Careers with Accounting</h2><p>Accounting qualifies for: Chartered Accounting, Auditing, Taxation, Finance, Business Management, Forensic Accounting.</p>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"Accounting Career Guide"}'
),

(
    'Courses I Can Study in South Africa When I Did English',
    'courses-i-can-study-south-africa-english',
    'Courses for English Students | Career Options South Africa',
    'Did English? Discover writing, media, and communication careers.',
    'static', 'Career Guidance', 'Grade 12', 'English Careers', 'informational',
    'courses for english students south africa', 'published',
    '<h2>Careers with English</h2><p>English leads to: Journalism, Creative Writing, Teaching, Marketing, Public Relations, Law, Media Studies, Publishing.</p>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"English Career Guide"}'
),

(
    'Courses I Can Study in South Africa When I Did Setswana',
    'courses-i-can-study-south-africa-setswana',
    'Courses for Setswana Students | Career Options South Africa',
    'Did Setswana? Explore language, teaching, and translation careers.',
    'static', 'Career Guidance', 'Grade 12', 'Setswana Careers', 'informational',
    'courses for setswana students south africa', 'published',
    '<h2>Careers with Setswana</h2><p>Setswana qualifies for: Teaching, Translation, Interpretation, Broadcasting, Publishing, Cultural Studies, Communications.</p>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"Setswana Career Guide"}'
),

(
    'Courses I Can Study in South Africa When I Did Life Orientation',
    'courses-i-can-study-south-africa-life-orientation',
    'Courses with Life Orientation | Career Options South Africa',
    'Did Life Orientation? Discover counseling, teaching, and social work careers.',
    'static', 'Career Guidance', 'Grade 12', 'Life Orientation Careers', 'informational',
    'courses with life orientation south africa', 'published',
    '<h2>Careers with Life Orientation</h2><p>Life Orientation supports: Psychology, Social Work, Counseling, Teaching, Human Resources, Community Development.</p>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"Life Orientation Career Guide"}'
),

-- 34-43. High paying jobs by subject
(
    'High Paying Jobs Which Have Geography That I Can Study After Matric',
    'high-paying-jobs-geography-south-africa',
    'High Paying Geography Careers | Salary Guide South Africa',
    'Discover high-paying careers that use Geography. Salary information and study paths.',
    'static', 'Career Guidance', 'Grade 12', 'Geography High Salary', 'informational',
    'high paying jobs geography south africa', 'published',
    '<h2>High Paying Geography Careers</h2><ul><li>Environmental Consultant (R300k-R800k)</li><li>Urban Planner (R250k-R600k)</li><li>GIS Specialist (R300k-R700k)</li><li>Meteorologist (R350k-R800k)</li><li>Geologist (R400k-R1M)</li></ul>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"High Paying Geography Careers"}'
),

(
    'High Paying Jobs that Require Life Science That I Can Study After Matric',
    'high-paying-jobs-life-science-south-africa',
    'High Paying Life Science Careers | Salary Guide',
    'Explore lucrative careers in life sciences with salary information.',
    'static', 'Career Guidance', 'Grade 12', 'Life Science High Salary', 'informational',
    'high paying jobs life science south africa', 'published',
    '<h2>High Paying Life Science Careers</h2><ul><li>Doctor (R600k-R2M+)</li><li>Pharmacist (R400k-R900k)</li><li>Physiotherapist (R350k-R800k)</li><li>Biotechnologist (R300k-R700k)</li><li>Veterinarian (R400k-R1M)</li></ul>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"High Paying Life Science Careers"}'
),

(
    'High Paying Jobs that Require Geography That I Can Study After Matric',
    'high-paying-careers-geography-study',
    'Best Paying Geography Careers to Study',
    'Top earning potential careers for geography students.',
    'static', 'Career Guidance', 'Grade 12', 'Geography Salary', 'informational',
    'high paying geography careers', 'published',
    '<h2>Best Paying Geography Careers</h2><p>Environmental consulting, Mining geology, Urban planning, Climate science.</p>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"Geography High Salary"}'
),

(
    'High Paying Jobs that Require English That I Can Study After Matric',
    'high-paying-jobs-english-south-africa',
    'High Paying English Careers | Salary Guide',
    'Discover well-paying careers for English students.',
    'static', 'Career Guidance', 'Grade 12', 'English High Salary', 'informational',
    'high paying jobs english south africa', 'published',
    '<h2>High Paying English Careers</h2><ul><li>Corporate Lawyer (R500k-R2M+)</li><li>Marketing Director (R500k-R1.5M)</li><li>Technical Writer (R300k-R700k)</li><li>Journalist/Editor (R250k-R600k)</li><li>University Professor (R400k-R1M)</li></ul>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"High Paying English Careers"}'
),

(
    'High Paying Jobs that Require Setswana That I Can Study After Matric',
    'high-paying-jobs-setswana-south-africa',
    'High Paying Setswana Careers | Salary Guide',
    'Explore lucrative careers using Setswana language skills.',
    'static', 'Career Guidance', 'Grade 12', 'Setswana High Salary', 'informational',
    'high paying jobs setswana south africa', 'published',
    '<h2>High Paying Setswana Careers</h2><ul><li>Legal Translator (R300k-R700k)</li><li>Broadcasting Director (R400k-R900k)</li><li>University Lecturer (R400k-R1M)</li><li>Government Communications (R350k-R800k)</li></ul>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"High Paying Setswana Careers"}'
),

(
    'High Paying Jobs that Require History That I Can Study After Matric',
    'high-paying-jobs-history-south-africa',
    'High Paying History Careers | Salary Guide',
    'Discover well-paying careers for history students.',
    'static', 'Career Guidance', 'Grade 12', 'History High Salary', 'informational',
    'high paying jobs history south africa', 'published',
    '<h2>High Paying History Careers</h2><ul><li>Lawyer (R500k-R2M+)</li><li>Museum Director (R400k-R900k)</li><li>Heritage Manager (R350k-R700k)</li><li>Political Analyst (R400k-R1M)</li><li>University Professor (R400k-R1M)</li></ul>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"High Paying History Careers"}'
),

(
    'High Paying Jobs that Require Accounting That I Can Study After Matric',
    'high-paying-jobs-accounting-south-africa',
    'High Paying Accounting Careers | Salary Guide',
    'Explore lucrative accounting and finance careers.',
    'static', 'Career Guidance', 'Grade 12', 'Accounting High Salary', 'informational',
    'high paying jobs accounting south africa', 'published',
    '<h2>High Paying Accounting Careers</h2><ul><li>Chartered Accountant (R600k-R2M+)</li><li>Financial Director (R800k-R3M+)</li><li>Audit Partner (R700k-R2.5M)</li><li>Tax Consultant (R500k-R1.5M)</li><li>Forensic Accountant (R500k-R1.5M)</li></ul>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"High Paying Accounting Careers"}'
),

(
    'High Paying Jobs that Require Business That I Can Study After Matric',
    'high-paying-jobs-business-south-africa',
    'High Paying Business Careers | Salary Guide',
    'Discover top-paying business and management careers.',
    'static', 'Career Guidance', 'Grade 12', 'Business High Salary', 'informational',
    'high paying jobs business south africa', 'published',
    '<h2>High Paying Business Careers</h2><ul><li>CEO/Managing Director (R1M-R10M+)</li><li>Marketing Director (R600k-R2M)</li><li>Operations Director (R700k-R2.5M)</li><li>Business Consultant (R500k-R1.5M)</li><li>Entrepreneur (Variable)</li></ul>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"High Paying Business Careers"}'
),

(
    'High Paying Jobs that Require Physical Science That I Can Study After Matric',
    'high-paying-jobs-physical-science-south-africa',
    'High Paying Physical Science Careers | Salary Guide',
    'Explore lucrative engineering and science careers.',
    'static', 'Career Guidance', 'Grade 12', 'Physical Science High Salary', 'informational',
    'high paying jobs physical science south africa', 'published',
    '<h2>High Paying Physical Science Careers</h2><ul><li>Engineer (R500k-R2M+)</li><li>Data Scientist (R600k-R1.5M)</li><li>Physicist (R400k-R1M)</li><li>Actuary (R700k-R2M+)</li><li>Petroleum Engineer (R800k-R3M)</li></ul>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"High Paying Physical Science Careers"}'
),

(
    'High Paying Jobs that Require Mathematics That I Can Study After Matric',
    'high-paying-jobs-mathematics-south-africa',
    'High Paying Mathematics Careers | Salary Guide',
    'Discover the best paying careers for mathematics students.',
    'static', 'Career Guidance', 'Grade 12', 'Mathematics High Salary', 'informational',
    'high paying jobs mathematics south africa', 'published',
    '<h2>High Paying Mathematics Careers</h2><ul><li>Actuary (R700k-R2M+)</li><li>Data Scientist (R600k-R1.5M)</li><li>Quantitative Analyst (R800k-R3M+)</li><li>Software Engineer (R500k-R1.5M)</li><li>Mathematician (R400k-R1M)</li></ul>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"High Paying Mathematics Careers"}'
),

-- 44. (skipped - empty in original list)

-- 45-51. General career and course pages
(
    'Best Courses to Study in South Africa in 2026',
    'best-courses-to-study-south-africa-2026',
    'Best Courses to Study in South Africa 2026 | Top Careers',
    'Discover the best courses to study in South Africa for 2026. High demand careers, salary information, and future prospects.',
    'static', 'Career Guidance', 'Grade 12', 'Best Courses 2026', 'informational',
    'best courses to study south africa 2026', 'published',
    '<h2>Best Courses for 2026</h2><h3>Technology</h3><ul><li>Computer Science</li><li>Data Science</li><li>Cybersecurity</li><li>AI and Machine Learning</li></ul><h3>Healthcare</h3><ul><li>Medicine</li><li>Nursing</li><li>Pharmacy</li><li>Physiotherapy</li></ul><h3>Business</h3><ul><li>Chartered Accounting</li><li>Finance</li><li>Business Management</li></ul><h3>Engineering</h3><ul><li>Software Engineering</li><li>Electrical Engineering</li><li>Renewable Energy Engineering</li></ul>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"Best Courses 2026"}'
),

(
    'Courses with High Job Opportunities in South Africa',
    'courses-high-job-opportunities-south-africa',
    'Courses with Best Job Opportunities | South Africa',
    'Find courses with the highest employment rates and job demand in South Africa.',
    'static', 'Career Guidance', 'Grade 12', 'Job Opportunities', 'informational',
    'courses with high job opportunities south africa', 'published',
    '<h2>High Demand Courses</h2><ul><li>Nursing and Healthcare</li><li>Teaching/Education</li><li>IT and Software Development</li><li>Engineering</li><li>Accounting and Finance</li><li>Renewable Energy</li><li>Data Analytics</li></ul>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"High Job Opportunity Courses"}'
),

(
    'Which Career is Right for Me Quiz South Africa',
    'which-career-is-right-for-me-quiz-south-africa',
    'Career Quiz South Africa | Find Your Perfect Career',
    'Take our free career quiz to discover which career path is right for you. Tailored for South African students.',
    'static', 'Career Guidance', 'Grade 12', 'Career Quiz', 'informational',
    'career quiz south africa', 'published',
    '<h2>Career Assessment Quiz</h2><p>Answer these questions to find your ideal career:</p><ol><li>What subjects do you enjoy most?</li><li>Do you prefer working indoors or outdoors?</li><li>Do you like working with people, data, or things?</li><li>What is your ideal salary?</li><li>How many years are you willing to study?</li></ol>',
    '{"@context":"https://schema.org","@type":"Quiz","name":"Career Quiz South Africa"}'
),

(
    'What to Study if You Like Maths in South Africa',
    'what-to-study-if-you-like-maths-south-africa',
    'Best Courses for Math Lovers | South Africa',
    'Love mathematics? Discover the best courses and careers for mathematically inclined students.',
    'static', 'Career Guidance', 'Grade 12', 'Maths Careers', 'informational',
    'what to study if you like maths south africa', 'published',
    '<h2>Courses for Math Lovers</h2><ul><li>Engineering (all fields)</li><li>Actuarial Science</li><li>Data Science</li><li>Computer Science</li><li>Finance</li><li>Physics</li><li>Mathematics</li><li>Statistics</li></ul>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"Maths Career Guide"}'
),

(
    'Best Degrees for Future Jobs in South Africa',
    'best-degrees-future-jobs-south-africa',
    'Best Degrees for Future Jobs | South Africa 2026-2035',
    'Future-proof your career with these degrees. High growth industries and emerging careers in South Africa.',
    'static', 'Career Guidance', 'Grade 12', 'Future Jobs', 'informational',
    'best degrees for future jobs south africa', 'published',
    '<h2>Future-Proof Degrees</h2><ul><li>Artificial Intelligence</li><li>Renewable Energy Engineering</li><li>Data Science</li><li>Biotechnology</li><li>Cybersecurity</li><li>Environmental Science</li><li>Healthcare</li></ul>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"Future Jobs Guide"}'
),

(
    'Easy Courses to Study After Matric',
    'easy-courses-to-study-after-matric',
    'Easy Courses to Study After Matric | Quick Qualifications',
    'Looking for easier courses to study after matric? Find accessible programs with good career prospects.',
    'static', 'Career Guidance', 'Grade 12', 'Easy Courses', 'informational',
    'easy courses to study after matric', 'published',
    '<h2>Accessible Courses</h2><ul><li>Business Administration</li><li>Marketing Management</li><li>Human Resources</li><li>Tourism Management</li><li>Office Administration</li><li>Retail Management</li></ul>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"Easy Courses Guide"}'
),

(
    'Courses that Pay Well in South Africa',
    'courses-that-pay-well-south-africa',
    'Courses that Pay Well | Highest Salaries South Africa',
    'Discover which courses lead to the highest paying careers in South Africa.',
    'static', 'Career Guidance', 'Grade 12', 'High Salary Courses', 'informational',
    'courses that pay well south africa', 'published',
    '<h2>Highest Paying Courses</h2><ul><li>Medicine (R600k-R2M+)</li><li>Actuarial Science (R700k-R2M+)</li><li>Engineering (R500k-R2M+)</li><li>Chartered Accounting (R600k-R2M+)</li><li>Law (R500k-R2M+)</li><li>Data Science (R600k-R1.5M)</li></ul>',
    '{"@context":"https://schema.org","@type":"EducationalOccupationalProgram","name":"High Salary Courses"}'
);
