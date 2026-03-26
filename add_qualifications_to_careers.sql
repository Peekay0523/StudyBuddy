-- Add qualifications to existing career_institutions records
-- This adds specific qualification names for each career-institution combination

-- Software Developer at UCT
UPDATE career_institutions 
SET subject_requirements = '{"subjects": [{"subject": "Mathematics", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}], "qualifications": [{"name": "BSc Computer Science", "type": "Degree", "duration": "3 years", "qualification_code": "UCT-CS-01"}, {"name": "BEng Electrical Engineering", "type": "Degree", "duration": "4 years", "qualification_code": "UCT-EE-01"}]}'
WHERE career_id = 7 AND institution_id = 1;

-- Software Developer at Wits
UPDATE career_institutions 
SET subject_requirements = '{"subjects": [{"subject": "Mathematics", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}], "qualifications": [{"name": "BSc Computer Science and Information Technology", "type": "Degree", "duration": "3 years", "qualification_code": "WITS-CS-01"}, {"name": "BSc Data Science", "type": "Degree", "duration": "3 years", "qualification_code": "WITS-DS-01"}]}'
WHERE career_id = 7 AND institution_id = 2;

-- Doctor at UCT
UPDATE career_institutions 
SET subject_requirements = '{"subjects": [{"subject": "Mathematics", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "Physical Sciences", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "Life Sciences", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "English", "level": 5, "description": "Level 5 (60-69%)"}], "qualifications": [{"name": "MBChB Bachelor of Medicine and Bachelor of Surgery", "type": "Degree", "duration": "6 years", "qualification_code": "UCT-MED-01"}]}'
WHERE career_id = 1 AND institution_id = 1;

-- Doctor at Wits
UPDATE career_institutions 
SET subject_requirements = '{"subjects": [{"subject": "Mathematics", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "Physical Sciences", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "Life Sciences", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "English", "level": 5, "description": "Level 5 (60-69%)"}], "qualifications": [{"name": "MBBCh Bachelor of Medicine and Bachelor of Surgery", "type": "Degree", "duration": "6 years", "qualification_code": "WITS-MED-01"}]}'
WHERE career_id = 1 AND institution_id = 2;

-- Engineer at Wits
UPDATE career_institutions 
SET subject_requirements = '{"subjects": [{"subject": "Mathematics", "level": 6, "description": "Level 6 (70-79%)"}, {"subject": "Physical Sciences", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}], "qualifications": [{"name": "BSc Engineering (Civil)", "type": "Degree", "duration": "4 years", "qualification_code": "WITS-ENG-CIV"}, {"name": "BSc Engineering (Mechanical)", "type": "Degree", "duration": "4 years", "qualification_code": "WITS-ENG-MEC"}, {"name": "BSc Engineering (Electrical)", "type": "Degree", "duration": "4 years", "qualification_code": "WITS-ENG-ELE"}]}'
WHERE career_id = 2 AND institution_id = 2;

-- Engineer at UP
UPDATE career_institutions 
SET subject_requirements = '{"subjects": [{"subject": "Mathematics", "level": 6, "description": "Level 6 (70-79%)"}, {"subject": "Physical Sciences", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}], "qualifications": [{"name": "BEng Civil Engineering", "type": "Degree", "duration": "4 years", "qualification_code": "UP-ENG-CIV"}, {"name": "BEng Mechanical Engineering", "type": "Degree", "duration": "4 years", "qualification_code": "UP-ENG-MEC"}, {"name": "BEng Chemical Engineering", "type": "Degree", "duration": "4 years", "qualification_code": "UP-ENG-CHE"}]}'
WHERE career_id = 2 AND institution_id = 4;

-- Teacher at UJ
UPDATE career_institutions 
SET subject_requirements = '{"subjects": [{"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}, {"subject": "Mathematics", "level": 3, "description": "Level 3 (40-49%)"}], "qualifications": [{"name": "Bachelor of Education (Foundation Phase)", "type": "Degree", "duration": "4 years", "qualification_code": "UJ-BED-FP"}, {"name": "Bachelor of Education (Intermediate Phase)", "type": "Degree", "duration": "4 years", "qualification_code": "UJ-BED-IP"}]}'
WHERE career_id = 3 AND institution_id = 9;

-- Teacher at NWU
UPDATE career_institutions 
SET subject_requirements = '{"subjects": [{"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}, {"subject": "Mathematics OR Mathematical Literacy", "level": 3, "description": "Level 3 (40-49%)"}], "qualifications": [{"name": "BEd Foundation Phase Teaching", "type": "Degree", "duration": "4 years", "qualification_code": "NWU-BED-FP"}, {"name": "BEd Intermediate Phase Teaching", "type": "Degree", "duration": "4 years", "qualification_code": "NWU-BED-IP"}]}'
WHERE career_id = 3 AND institution_id = 11;

-- Accountant at UCT
UPDATE career_institutions 
SET subject_requirements = '{"subjects": [{"subject": "Mathematics", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}, {"subject": "Accounting", "level": 5, "description": "Level 5 (60-69%) - Recommended"}], "qualifications": [{"name": "BCom Accounting Sciences", "type": "Degree", "duration": "3 years", "qualification_code": "UCT-ACC-01"}, {"name": "BCom Finance and Tax", "type": "Degree", "duration": "3 years", "qualification_code": "UCT-FIN-01"}]}'
WHERE career_id = 4 AND institution_id = 1;

-- Accountant at Wits
UPDATE career_institutions 
SET subject_requirements = '{"subjects": [{"subject": "Mathematics", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}], "qualifications": [{"name": "BCom Accounting Sciences", "type": "Degree", "duration": "3 years", "qualification_code": "WITS-ACC-01"}, {"name": "BCom Financial Accounting", "type": "Degree", "duration": "3 years", "qualification_code": "WITS-FA-01"}]}'
WHERE career_id = 4 AND institution_id = 2;

-- Nurse at UKZN
UPDATE career_institutions 
SET subject_requirements = '{"subjects": [{"subject": "Mathematics", "level": 4, "description": "Level 4 (50-59%)"}, {"subject": "Life Sciences", "level": 4, "description": "Level 4 (50-59%)"}, {"subject": "English", "level": 4, "description": "Level 4 (50-59%)"}], "qualifications": [{"name": "Bachelor of Nursing (Professional)", "type": "Degree", "duration": "4 years", "qualification_code": "UKZN-NUR-01"}, {"name": "Diploma in Nursing (Higher)", "type": "Diploma", "duration": "3 years", "qualification_code": "UKZN-NUR-DIP"}]}'
WHERE career_id = 6 AND institution_id = 5;

-- Electrician at CPUT
UPDATE career_institutions 
SET subject_requirements = '{"subjects": [{"subject": "Mathematics", "level": 3, "description": "Level 3 (40-49%)"}, {"subject": "Physical Sciences", "level": 3, "description": "Level 3 (40-49%)"}, {"subject": "English", "level": 3, "description": "Level 3 (40-49%)"}], "qualifications": [{"name": "National Diploma in Electrical Engineering", "type": "Diploma", "duration": "3 years", "qualification_code": "CPUT-ELC-DIP"}, {"name": "Higher Certificate in Electrical Engineering", "type": "Certificate", "duration": "1 year", "qualification_code": "CPUT-ELC-HC"}]}'
WHERE career_id = 12 AND institution_id = 6;

-- Chef at CPUT
UPDATE career_institutions 
SET subject_requirements = '{"subjects": [{"subject": "English", "level": 3, "description": "Level 3 (40-49%)"}, {"subject": "Mathematics OR Mathematical Literacy", "level": 2, "description": "Level 2 (30-39%)"}], "qualifications": [{"name": "Diploma in Culinary Arts", "type": "Diploma", "duration": "3 years", "qualification_code": "CPUT-CHEF-DIP"}, {"name": "Higher Certificate in Food Preparation", "type": "Certificate", "duration": "1 year", "qualification_code": "CPUT-CHEF-HC"}]}'
WHERE career_id = 13 AND institution_id = 6;

-- Lawyer at UCT
UPDATE career_institutions 
SET subject_requirements = '{"subjects": [{"subject": "English", "level": 6, "description": "Level 6 (70-79%)"}, {"subject": "Mathematics OR Mathematical Literacy", "level": 4, "description": "Level 4 (50-59%)"}], "qualifications": [{"name": "LLB Bachelor of Laws", "type": "Degree", "duration": "4 years", "qualification_code": "UCT-LLB-01"}, {"name": "BCom Law", "type": "Degree", "duration": "3 years", "qualification_code": "UCT-LAW-01"}]}'
WHERE career_id = 5 AND institution_id = 1;

-- Lawyer at UP
UPDATE career_institutions 
SET subject_requirements = '{"subjects": [{"subject": "English", "level": 5, "description": "Level 5 (60-69%)"}, {"subject": "Mathematics OR Mathematical Literacy", "level": 4, "description": "Level 4 (50-59%)"}], "qualifications": [{"name": "LLB Bachelor of Laws", "type": "Degree", "duration": "4 years", "qualification_code": "UP-LLB-01"}, {"name": "BCom Law", "type": "Degree", "duration": "3 years", "qualification_code": "UP-LAW-01"}]}'
WHERE career_id = 5 AND institution_id = 4;
