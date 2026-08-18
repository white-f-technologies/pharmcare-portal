<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Pain Relief & Analgesics',
                'slug' => 'painkillers-analgesics',
                'description' => 'Medications for pain relief, headaches, inflammation, and fever reduction (e.g., Paracetamol, Ibuprofen, Diclofenac, Tramadol).',
            ],
            [
                'name' => 'Antibiotics & Antimicrobials',
                'slug' => 'antibiotics-antimicrobials',
                'description' => 'Prescription antibacterial agents for bacterial infections (e.g., Amoxicillin, Azithromycin, Ciprofloxacin, Cefixime).',
            ],
            [
                'name' => 'Antimalarials & Antiparasitics',
                'slug' => 'antimalarials-antiparasitics',
                'description' => 'Malaria treatment & prophylaxis (e.g., Artemether-Lumefantrine, Quinine) and deworming anthelmintics (Albendazole, Mebendazole).',
            ],
            [
                'name' => 'Cough, Cold, Flu & Respiratory',
                'slug' => 'cough-cold-respiratory',
                'description' => 'Decongestants, expectorants, cough syrups, bronchodilators, and antiasthmatic inhalers (e.g., Salbutamol, Fluticasone).',
            ],
            [
                'name' => 'Allergy & Antihistamines',
                'slug' => 'allergy-antihistamines',
                'description' => 'Antihistamines for allergic rhinitis, hives, itching, and insect bites (e.g., Cetirizine, Loratadine, Chlorpheniramine).',
            ],
            [
                'name' => 'Gastrointestinal & Digestive Health',
                'slug' => 'gastrointestinal-digestive',
                'description' => 'Antacids, proton pump inhibitors (PPIs), anti-ulcer drugs, anti-diarrheals, and laxatives (e.g., Omeprazole, Esomeprazole, Loperamide).',
            ],
            [
                'name' => 'Cardiovascular & Antihypertensives',
                'slug' => 'cardiovascular-antihypertensives',
                'description' => 'Blood pressure regulators, beta-blockers, ACE inhibitors, diuretics, and statins (e.g., Amlodipine, Lisinopril, Losartan, Atorvastatin).',
            ],
            [
                'name' => 'Diabetes & Endocrine Care',
                'slug' => 'diabetes-endocrine',
                'description' => 'Blood glucose regulators, insulins, oral hypoglycemic agents, and thyroid therapies (e.g., Metformin, Glibenclamide, Levothyroxine).',
            ],
            [
                'name' => 'Vitamins, Minerals & Supplements',
                'slug' => 'vitamins-supplements',
                'description' => 'Nutritional boosters, multivitamins, calcium, iron, folic acid, zinc, omega-3, and prenatal formulations.',
            ],
            [
                'name' => 'Dermatology & Skin Care',
                'slug' => 'dermatology-skin-care',
                'description' => 'Topical ointments, medicated creams, corticosteroids, antiseptic washes, and wound management preparations.',
            ],
            [
                'name' => 'Antifungals & Antimycotics',
                'slug' => 'antifungals-antimycotics',
                'description' => 'Topical and oral treatments for fungal infections and candidiasis (e.g., Fluconazole, Clotrimazole, Ketoconazole).',
            ],
            [
                'name' => 'Antivirals & Antiretrovirals',
                'slug' => 'antivirals-antiretrovirals',
                'description' => 'Medications for viral infections, herpes, influenza, and antiretroviral therapy (e.g., Acyclovir, Oseltamivir).',
            ],
            [
                'name' => 'Ophthalmic & ENT Care',
                'slug' => 'ophthalmic-ent-care',
                'description' => 'Medicated eye drops, optic ointments, ear drops, and nasal decongestant sprays.',
            ],
            [
                'name' => 'Central Nervous System & Neurology',
                'slug' => 'cns-psychiatric-neurology',
                'description' => 'Anticonvulsants, sedatives, antidepressants, anxiolytics, and migraine management therapies.',
            ],
            [
                'name' => 'Women\'s Health & Family Planning',
                'slug' => 'womens-health-contraceptives',
                'description' => 'Contraceptives, pregnancy tests, hormone replacement, fertility aids, and maternal health products.',
            ],
            [
                'name' => 'Men\'s Health & Urologicals',
                'slug' => 'mens-health-urologicals',
                'description' => 'Prostate health formulations, urinary tract antispasmodics, and urological medications.',
            ],
            [
                'name' => 'Pediatric & Child Health',
                'slug' => 'pediatric-child-health',
                'description' => 'Pediatric drops, infant syrups, oral rehydration salts (ORS), and child-specific formulations.',
            ],
            [
                'name' => 'First Aid, Surgical & Medical Supplies',
                'slug' => 'first-aid-medical-supplies',
                'description' => 'Bandages, sterile gauze, syringes, needles, surgical gloves, alcohol swabs, cannulas, and dressing kits.',
            ],
            [
                'name' => 'Musculoskeletal & Joint Care',
                'slug' => 'musculoskeletal-joint-care',
                'description' => 'Muscle relaxants, arthritis care, topical pain gels, rubefacients, and joint lubrication supplements.',
            ],
            [
                'name' => 'Hematology & Blood Health',
                'slug' => 'hematology-blood-products',
                'description' => 'Hematinics, blood boosters, antiplatelet medications (Aspirin 75mg), and anticoagulants.',
            ],
            [
                'name' => 'Anesthetics & Preoperative Care',
                'slug' => 'anesthetics-preoperative',
                'description' => 'Local anesthetics (Lidocaine/Lignocaine), topical numbing gels, and antiseptic surgical scrubs.',
            ],
            [
                'name' => 'Herbal & Natural Remedies',
                'slug' => 'herbal-natural-remedies',
                'description' => 'Approved natural herbal extracts, botanical teas, herbal cough syrups, and essential oils.',
            ],
            [
                'name' => 'Personal Hygiene & Sanitizers',
                'slug' => 'personal-hygiene-cosmetics',
                'description' => 'Antiseptic soaps, hand sanitizers, disinfectant solutions, medicated body washes, and hygiene supplies.',
            ],
            [
                'name' => 'Vaccines, Serums & Biologicals',
                'slug' => 'vaccines-biologicals',
                'description' => 'Tetanus toxoid, vaccines, immunoglobulins, antivenom serums, and biological agents.',
            ],
        ];

        foreach ($categories as $cat) {
            // First check if existing slug or name exists to prevent duplicates
            $existing = Category::where('slug', $cat['slug'])
                ->orWhere('name', $cat['name'])
                ->first();

            if ($existing) {
                $existing->update([
                    'name' => $cat['name'],
                    'description' => $cat['description'],
                ]);
            } else {
                Category::create($cat);
            }
        }
    }
}
