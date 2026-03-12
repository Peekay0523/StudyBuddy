<?php
/**
 * SEO Pages Setup Script
 * Run this to initialize the SEO system and create sample pages
 * 
 * Usage: php setup-seo-pages.php
 */

require_once __DIR__ . '/config/database.php';

echo "==============================================\n";
echo "  SEO Long-tail Pages Setup\n";
echo "==============================================\n\n";

try {
    $db = Database::getInstance()->getConnection();
    echo "✓ Connected to database\n\n";
    
    // Read and execute the SEO schema
    echo "Creating SEO tables...\n";
    $schemaFile = __DIR__ . '/create_seo_pages_table.sql';
    
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: $schemaFile");
    }
    
    $schema = file_get_contents($schemaFile);
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    $tablesCreated = 0;
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $db->exec($statement);
                $tablesCreated++;
            } catch (PDOException $e) {
                // Ignore errors for existing tables
                if (strpos($e->getMessage(), 'already exists') === false) {
                    echo "  Warning: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    echo "✓ Created $tablesCreated table(s)\n\n";
    
    // Insert sample SEO pages
    echo "Inserting sample SEO pages...\n";
    
    $samplePages = [
        [
            'title' => 'Mathematics Memorandum for Grade 12 - Full Answers with Step-by-Step Solutions',
            'slug' => 'math-memorandum-grade-12-full-answers',
            'meta_title' => 'Math Memorandum for Grade 12 Full Answers | Complete Solutions',
            'meta_description' => 'Complete mathematics memorandum for Grade 12 with full answers, step-by-step solutions, and worked examples. CAPS curriculum aligned for South African students.',
            'meta_keywords' => 'math memorandum grade 12, mathematics full answers, grade 12 math solutions, CAPS math memorandum',
            'content_type' => 'hybrid',
            'subject' => 'Mathematics',
            'grade_level' => 'Grade 12',
            'topic' => 'Complete Memorandum',
            'search_intent' => 'informational',
            'target_keyword' => 'math memorandum for grade 12 full answers',
            'secondary_keywords' => ['grade 12 math answers', 'mathematics memorandum CAPS', 'matric math past papers'],
            'status' => 'published',
            'full_content' => generateMathContent(),
            'schema_markup' => json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'LearningResource',
                'name' => 'Mathematics Memorandum for Grade 12',
                'educationalLevel' => 'Grade 12',
                'inLanguage' => 'en-ZA'
            ])
        ],
        [
            'title' => 'Mathematical Literacy Grade 12 Finance Memorandum - Questions and Answers',
            'slug' => 'math-lit-grade-12-finance-memorandum',
            'meta_title' => 'Mathematical Literacy Grade 12 Finance Memorandum | Full Answers',
            'meta_description' => 'Grade 12 Mathematical Literacy finance questions with complete answers. Learn interest, depreciation, loans, and investments with worked solutions.',
            'meta_keywords' => 'math lit finance grade 12, mathematical literacy memorandum, finance questions and answers',
            'content_type' => 'hybrid',
            'subject' => 'Mathematical Literacy',
            'grade_level' => 'Grade 12',
            'topic' => 'Finance',
            'search_intent' => 'informational',
            'target_keyword' => 'mathematical literacy grade 12 finance memorandum',
            'secondary_keywords' => ['math lit finance questions', 'grade 12 finance answers', 'interest calculations'],
            'status' => 'published',
            'full_content' => generateMathLitFinanceContent(),
            'schema_markup' => json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'LearningResource',
                'name' => 'Mathematical Literacy Finance Memorandum',
                'educationalLevel' => 'Grade 12',
                'inLanguage' => 'en-ZA'
            ])
        ],
        [
            'title' => 'Physical Sciences Grade 12 Physics Memorandum - Mechanics and Electricity',
            'slug' => 'physical-sciences-grade-12-physics-memorandum',
            'meta_title' => 'Physical Sciences Grade 12 Physics Memorandum | Complete Answers',
            'meta_description' => 'Grade 12 Physical Sciences physics memorandum with full solutions for mechanics, electricity, waves, and light. CAPS aligned with detailed explanations.',
            'meta_keywords' => 'physical sciences grade 12 physics, physics memorandum, mechanics answers, electricity problems',
            'content_type' => 'hybrid',
            'subject' => 'Physical Sciences',
            'grade_level' => 'Grade 12',
            'topic' => 'Physics - Mechanics & Electricity',
            'search_intent' => 'informational',
            'target_keyword' => 'physical sciences grade 12 physics memorandum',
            'secondary_keywords' => ['grade 12 mechanics answers', 'physics past papers', 'electricity calculations'],
            'status' => 'published',
            'full_content' => generatePhysicsContent(),
            'schema_markup' => json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'LearningResource',
                'name' => 'Physical Sciences Physics Memorandum',
                'educationalLevel' => 'Grade 12',
                'inLanguage' => 'en-ZA'
            ])
        ],
        [
            'title' => 'Life Sciences Grade 12 DNA and Genetics Study Guide with Practice Questions',
            'slug' => 'life-sciences-grade-12-dna-genetics-study-guide',
            'meta_title' => 'Life Sciences Grade 12 DNA and Genetics Study Guide | Complete Answers',
            'meta_description' => 'Comprehensive Life Sciences Grade 12 DNA and genetics study guide with practice questions, answers, and detailed explanations. CAPS curriculum aligned.',
            'meta_keywords' => 'life sciences grade 12 DNA, genetics study guide, DNA replication, protein synthesis',
            'content_type' => 'hybrid',
            'subject' => 'Life Sciences',
            'grade_level' => 'Grade 12',
            'topic' => 'DNA and Genetics',
            'search_intent' => 'informational',
            'target_keyword' => 'life sciences grade 12 DNA and genetics',
            'secondary_keywords' => ['DNA replication grade 12', 'protein synthesis', 'genetics problems'],
            'status' => 'published',
            'full_content' => generateBiologyContent(),
            'schema_markup' => json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'LearningResource',
                'name' => 'Life Sciences DNA and Genetics Guide',
                'educationalLevel' => 'Grade 12',
                'inLanguage' => 'en-ZA'
            ])
        ],
        [
            'title' => 'Grade 12 Calculus Questions and Answers - Differentiation and Integration',
            'slug' => 'grade-12-calculus-questions-answers-differentiation-integration',
            'meta_title' => 'Grade 12 Calculus Questions and Answers PDF | Differentiation & Integration',
            'meta_description' => 'Grade 12 calculus questions with complete answers covering differentiation, integration, limits, and applications. Download PDF with step-by-step solutions.',
            'meta_keywords' => 'grade 12 calculus questions, calculus answers pdf, differentiation, integration problems',
            'content_type' => 'hybrid',
            'subject' => 'Mathematics',
            'grade_level' => 'Grade 12',
            'topic' => 'Calculus',
            'search_intent' => 'informational',
            'target_keyword' => 'grade 12 calculus questions and answers pdf',
            'secondary_keywords' => ['calculus grade 12 past exam questions', 'differentiation problems', 'integration techniques'],
            'status' => 'published',
            'full_content' => generateCalculusContent(),
            'schema_markup' => json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'LearningResource',
                'name' => 'Grade 12 Calculus Questions and Answers',
                'educationalLevel' => 'Grade 12',
                'inLanguage' => 'en-ZA'
            ])
        ]
    ];
    
    $insertStmt = $db->prepare("
        INSERT OR IGNORE INTO seo_pages (
            title, slug, meta_title, meta_description, meta_keywords,
            content_type, subject, grade_level, topic, search_intent,
            target_keyword, secondary_keywords, full_content,
            schema_markup, status, published_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
    ");
    
    $pagesCreated = 0;
    foreach ($samplePages as $page) {
        $result = $insertStmt->execute([
            $page['title'],
            $page['slug'],
            $page['meta_title'],
            $page['meta_description'],
            $page['meta_keywords'],
            $page['content_type'],
            $page['subject'],
            $page['grade_level'],
            $page['topic'],
            $page['search_intent'],
            $page['target_keyword'],
            json_encode($page['secondary_keywords']),
            $page['full_content'],
            $page['schema_markup'],
            $page['status']
        ]);
        
        if ($result) {
            $pagesCreated++;
            echo "  ✓ Created: {$page['slug']}\n";
        }
    }
    
    echo "\n✓ Created $pagesCreated sample pages\n\n";
    
    // Summary
    echo "==============================================\n";
    echo "  Setup Complete!\n";
    echo "==============================================\n\n";
    echo "Next steps:\n";
    echo "1. Visit /admin/seo/pages to manage your SEO pages\n";
    echo "2. Visit /seo to browse all published pages\n";
    echo "3. Visit /seo/sitemap.xml for your XML sitemap\n";
    echo "4. Use /admin/seo/generate to create more pages with AI\n\n";
    
    echo "Sample URLs:\n";
    foreach ($samplePages as $page) {
        echo "  - /seo/{$page['slug']}\n";
    }
    echo "\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

/**
 * Generate sample mathematics content
 */
function generateMathContent() {
    return '
<h2>Mathematics Grade 12 Complete Memorandum</h2>
<p>This comprehensive memorandum covers all major topics in the Grade 12 Mathematics CAPS curriculum, including Algebra, Calculus, Geometry, Trigonometry, Statistics, and Probability.</p>

<h2>Algebra</h2>
<h3>Question 1: Quadratic Equations</h3>
<p><strong>Solve for x:</strong> 2x² - 5x - 3 = 0</p>
<p><strong>Solution:</strong></p>
<ul>
<li>Using the quadratic formula: x = (-b ± √(b² - 4ac)) / 2a</li>
<li>a = 2, b = -5, c = -3</li>
<li>x = (5 ± √(25 + 24)) / 4</li>
<li>x = (5 ± 7) / 4</li>
<li>x = 3 or x = -½</li>
</ul>

<h3>Question 2: Simultaneous Equations</h3>
<p><strong>Solve:</strong><br>
3x + 2y = 12<br>
x - y = 1</p>
<p><strong>Solution:</strong></p>
<ul>
<li>From equation 2: x = y + 1</li>
<li>Substitute into equation 1: 3(y + 1) + 2y = 12</li>
<li>3y + 3 + 2y = 12</li>
<li>5y = 9</li>
<li>y = 1.8</li>
<li>x = 2.8</li>
</ul>

<h2>Calculus</h2>
<h3>Question 3: Differentiation</h3>
<p><strong>Find the derivative of:</strong> f(x) = 3x³ - 2x² + 5x - 1</p>
<p><strong>Solution:</strong></p>
<ul>
<li>f\'(x) = 9x² - 4x + 5</li>
</ul>

<h3>Question 4: Integration</h3>
<p><strong>Find:</strong> ∫(4x³ - 3x² + 2)dx</p>
<p><strong>Solution:</strong></p>
<ul>
<li>= x⁴ - x³ + 2x + C</li>
</ul>

<h2>Trigonometry</h2>
<h3>Question 5: Trigonometric Identities</h3>
<p><strong>Prove:</strong> sin²θ + cos²θ = 1</p>
<p><strong>Solution:</strong></p>
<ul>
<li>This is the fundamental trigonometric identity derived from the Pythagorean theorem</li>
<li>In a unit circle, x² + y² = 1</li>
<li>Since x = cosθ and y = sinθ, we have sin²θ + cos²θ = 1</li>
</ul>

<h2>Common Mistakes to Avoid</h2>
<ul>
<li>Forgetting the ± when taking square roots</li>
<li>Not checking solutions in the original equation</li>
<li>Misapplying the quadratic formula</li>
<li>Forgetting the constant of integration (+C)</li>
</ul>

<h2>Exam Tips</h2>
<ul>
<li>Show all your working steps clearly</li>
<li>Check your answers by substituting back</li>
<li>Manage your time - don\'t spend too long on one question</li>
<li>Practice past papers under exam conditions</li>
</ul>
';
}

function generateMathLitFinanceContent() {
    return '
<h2>Mathematical Literacy Grade 12 - Finance</h2>
<p>Complete memorandum for Finance topics including interest calculations, loans, investments, and depreciation.</p>

<h2>Simple and Compound Interest</h2>
<h3>Question 1: Simple Interest</h3>
<p><strong>Calculate the simple interest on R10,000 invested at 8% p.a. for 5 years.</strong></p>
<p><strong>Solution:</strong></p>
<ul>
<li>Formula: SI = P × i × n</li>
<li>P = 10,000, i = 0.08, n = 5</li>
<li>SI = 10,000 × 0.08 × 5</li>
<li>SI = R4,000</li>
<li>Accumulated amount = P + SI = R14,000</li>
</ul>

<h3>Question 2: Compound Interest</h3>
<p><strong>Calculate the compound interest on R15,000 invested at 12% p.a. compounded monthly for 3 years.</strong></p>
<p><strong>Solution:</strong></p>
<ul>
<li>Formula: A = P(1 + i)^n</li>
<li>P = 15,000, i = 0.12/12 = 0.01, n = 3 × 12 = 36</li>
<li>A = 15,000(1.01)^36</li>
<li>A = 15,000 × 1.43077</li>
<li>A = R21,461.55</li>
<li>Interest earned = R6,461.55</li>
</ul>

<h2>Depreciation</h2>
<h3>Question 3: Straight-line Depreciation</h3>
<p><strong>A car worth R250,000 depreciates at 15% p.a. using the straight-line method. Calculate its value after 4 years.</strong></p>
<p><strong>Solution:</strong></p>
<ul>
<li>Formula: A = P(1 - in)</li>
<li>A = 250,000(1 - 0.15 × 4)</li>
<li>A = 250,000(0.4)</li>
<li>A = R100,000</li>
</ul>

<h2>Loans and Investments</h2>
<h3>Question 4: Monthly Installments</h3>
<p><strong>Calculate the monthly installment on a loan of R200,000 at 18% p.a. compounded monthly over 20 years.</strong></p>
<p><strong>Solution:</strong></p>
<ul>
<li>Use the present value formula: P = x[1 - (1 + i)^-n] / i</li>
<li>Rearrange to find x (monthly payment)</li>
<li>x = P × i / [1 - (1 + i)^-n]</li>
<li>x = 200,000 × 0.015 / [1 - (1.015)^-240]</li>
<li>x = R3,082.77 per month</li>
</ul>

<h2>Common Mistakes</h2>
<ul>
<li>Confusing simple and compound interest formulas</li>
<li>Not converting annual rate to monthly rate</li>
<li>Forgetting to convert years to months</li>
<li>Using wrong depreciation formula</li>
</ul>
';
}

function generatePhysicsContent() {
    return '
<h2>Physical Sciences Grade 12 - Physics Memorandum</h2>
<p>Complete solutions for Mechanics, Electricity, Waves, and Light topics.</p>

<h2>Mechanics</h2>
<h3>Question 1: Newton\'s Second Law</h3>
<p><strong>A 5 kg object is pushed with a force of 20 N. Calculate its acceleration.</strong></p>
<p><strong>Solution:</strong></p>
<ul>
<li>F = ma</li>
<li>20 = 5a</li>
<li>a = 4 m/s²</li>
</ul>

<h3>Question 2: Work, Energy and Power</h3>
<p><strong>Calculate the work done when a force of 50 N moves an object 10 m in the direction of the force.</strong></p>
<p><strong>Solution:</strong></p>
<ul>
<li>W = F × d</li>
<li>W = 50 × 10</li>
<li>W = 500 J</li>
</ul>

<h2>Electricity</h2>
<h3>Question 3: Ohm\'s Law</h3>
<p><strong>A resistor has a resistance of 10 Ω. Calculate the current when a potential difference of 24 V is applied across it.</strong></p>
<p><strong>Solution:</strong></p>
<ul>
<li>V = IR</li>
<li>24 = I × 10</li>
<li>I = 2.4 A</li>
</ul>

<h3>Question 4: Series and Parallel Circuits</h3>
<p><strong>Three resistors of 4 Ω, 6 Ω, and 12 Ω are connected in parallel. Calculate the equivalent resistance.</strong></p>
<p><strong>Solution:</strong></p>
<ul>
<li>1/Rp = 1/4 + 1/6 + 1/12</li>
<li>1/Rp = 3/12 + 2/12 + 1/12</li>
<li>1/Rp = 6/12</li>
<li>Rp = 2 Ω</li>
</ul>

<h2>Formulas to Remember</h2>
<ul>
<li>F = ma (Newton\'s Second Law)</li>
<li>W = Fd (Work)</li>
<li>Ek = ½mv² (Kinetic Energy)</li>
<li>Ep = mgh (Gravitational Potential Energy)</li>
<li>V = IR (Ohm\'s Law)</li>
<li>P = VI (Electric Power)</li>
</ul>
';
}

function generateBiologyContent() {
    return '
<h2>Life Sciences Grade 12 - DNA and Genetics</h2>
<p>Comprehensive study guide covering DNA structure, replication, protein synthesis, and genetics.</p>

<h2>DNA Structure</h2>
<h3>Question 1: Describe the structure of DNA</h3>
<p><strong>Answer:</strong></p>
<ul>
<li>DNA is a double helix structure</li>
<li>Made of two polynucleotide strands</li>
<li>Each nucleotide consists of: deoxyribose sugar, phosphate group, and nitrogenous base</li>
<li>Bases: Adenine (A), Thymine (T), Guanine (G), Cytosine (C)</li>
<li>A pairs with T (2 hydrogen bonds)</li>
<li>G pairs with C (3 hydrogen bonds)</li>
<li>Strands are antiparallel</li>
</ul>

<h2>DNA Replication</h2>
<h3>Question 2: Explain the process of DNA replication</h3>
<p><strong>Answer:</strong></p>
<ul>
<li>Occurs during interphase (S phase)</li>
<li>DNA helicase unwinds and unzips the double helix</li>
<li>Hydrogen bonds between bases break</li>
<li>Each strand serves as a template</li>
<li>DNA polymerase adds complementary nucleotides</li>
<li>Results in two identical DNA molecules</li>
<li>Semi-conservative process</li>
</ul>

<h2>Protein Synthesis</h2>
<h3>Question 3: Describe transcription</h3>
<p><strong>Answer:</strong></p>
<ul>
<li>Occurs in the nucleus</li>
<li>DNA unwinds to expose the gene</li>
<li>mRNA is synthesized using DNA as template</li>
<li>RNA polymerase builds mRNA strand</li>
<li>mRNA carries genetic code to ribosome</li>
</ul>

<h3>Question 4: Describe translation</h3>
<p><strong>Answer:</strong></p>
<ul>
<li>Occurs at ribosomes in cytoplasm</li>
<li>mRNA attaches to ribosome</li>
<li>tRNA molecules bring amino acids</li>
<li>Anticodon on tRNA pairs with codon on mRNA</li>
<li>Amino acids join to form polypeptide chain</li>
<li>Process continues until stop codon is reached</li>
</ul>

<h2>Genetics Problems</h2>
<h3>Question 5: Monohybrid Cross</h3>
<p><strong>In humans, brown eyes (B) are dominant over blue eyes (b). If a heterozygous brown-eyed person marries a blue-eyed person, what are the possible genotypes and phenotypes of their children?</strong></p>
<p><strong>Solution:</strong></p>
<ul>
<li>Parent 1: Bb (heterozygous brown)</li>
<li>Parent 2: bb (blue)</li>
<li>Punnett Square: Bb × bb</li>
<li>Offspring: 50% Bb (brown), 50% bb (blue)</li>
<li>Phenotypic ratio: 1 brown : 1 blue</li>
</ul>

<h2>Key Terms</h2>
<ul>
<li><strong>Gene:</strong> Unit of heredity</li>
<li><strong>Allele:</strong> Different forms of a gene</li>
<li><strong>Genotype:</strong> Genetic makeup</li>
<li><strong>Phenotype:</strong> Physical appearance</li>
<li><strong>Homozygous:</strong> Same alleles (BB or bb)</li>
<li><strong>Heterozygous:</strong> Different alleles (Bb)</li>
</ul>
';
}

function generateCalculusContent() {
    return '
<h2>Grade 12 Calculus - Questions and Answers</h2>
<p>Complete guide to differentiation and integration with step-by-step solutions.</p>

<h2>Differentiation</h2>
<h3>Question 1: First Principles</h3>
<p><strong>Find the derivative of f(x) = x² using first principles.</strong></p>
<p><strong>Solution:</strong></p>
<ul>
<li>f\'(x) = lim h→0 [f(x+h) - f(x)] / h</li>
<li>= lim h→0 [(x+h)² - x²] / h</li>
<li>= lim h→0 [x² + 2xh + h² - x²] / h</li>
<li>= lim h→0 [2xh + h²] / h</li>
<li>= lim h→0 [2x + h]</li>
<li>f\'(x) = 2x</li>
</ul>

<h3>Question 2: Chain Rule</h3>
<p><strong>Differentiate: y = (3x² + 1)⁴</strong></p>
<p><strong>Solution:</strong></p>
<ul>
<li>Let u = 3x² + 1, then y = u⁴</li>
<li>dy/du = 4u³</li>
<li>du/dx = 6x</li>
<li>dy/dx = dy/du × du/dx</li>
<li>= 4u³ × 6x</li>
<li>= 24x(3x² + 1)³</li>
</ul>

<h3>Question 3: Product Rule</h3>
<p><strong>Differentiate: y = x² sin(x)</strong></p>
<p><strong>Solution:</strong></p>
<ul>
<li>y\' = u\'v + uv\'</li>
<li>u = x², v = sin(x)</li>
<li>u\' = 2x, v\' = cos(x)</li>
<li>y\' = 2x sin(x) + x² cos(x)</li>
</ul>

<h2>Integration</h2>
<h3>Question 4: Basic Integration</h3>
<p><strong>Find: ∫(5x⁴ - 3x² + 2x)dx</strong></p>
<p><strong>Solution:</strong></p>
<ul>
<li>= x⁵ - x³ + x² + C</li>
</ul>

<h3>Question 5: Definite Integral</h3>
<p><strong>Evaluate: ∫₀² (3x² + 2x)dx</strong></p>
<p><strong>Solution:</strong></p>
<ul>
<li>= [x³ + x²]₀²</li>
<li>= (8 + 4) - (0 + 0)</li>
<li>= 12</li>
</ul>

<h2>Applications of Calculus</h2>
<h3>Question 6: Finding Maxima and Minima</h3>
<p><strong>Find the turning points of y = x³ - 3x + 2</strong></p>
<p><strong>Solution:</strong></p>
<ul>
<li>dy/dx = 3x² - 3</li>
<li>Set dy/dx = 0: 3x² - 3 = 0</li>
<li>x² = 1, so x = 1 or x = -1</li>
<li>When x = 1: y = 0 (minimum)</li>
<li>When x = -1: y = 4 (maximum)</li>
</ul>

<h2>Important Formulas</h2>
<ul>
<li>d/dx(xⁿ) = nxⁿ⁻¹</li>
<li>∫xⁿ dx = xⁿ⁺¹/(n+1) + C (n ≠ -1)</li>
<li>Product Rule: (uv)\' = u\'v + uv\'</li>
<li>Quotient Rule: (u/v)\' = (u\'v - uv\')/v²</li>
<li>Chain Rule: dy/dx = dy/du × du/dx</li>
</ul>
';
}
