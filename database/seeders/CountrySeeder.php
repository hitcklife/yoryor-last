<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            // A
            ['name' => 'Afghanistan', 'code' => 'AF', 'flag' => '🇦🇫', 'phone_code' => '+93', 'phone_template' => '## ### ####'],
            ['name' => 'Albania', 'code' => 'AL', 'flag' => '🇦🇱', 'phone_code' => '+355', 'phone_template' => '## ### ####'],
            ['name' => 'Algeria', 'code' => 'DZ', 'flag' => '🇩🇿', 'phone_code' => '+213', 'phone_template' => '## ## ## ## ##'],
            ['name' => 'Andorra', 'code' => 'AD', 'flag' => '🇦🇩', 'phone_code' => '+376', 'phone_template' => '### ###'],
            ['name' => 'Angola', 'code' => 'AO', 'flag' => '🇦🇴', 'phone_code' => '+244', 'phone_template' => '### ### ###'],
            ['name' => 'Antigua and Barbuda', 'code' => 'AG', 'flag' => '🇦🇬', 'phone_code' => '+1268', 'phone_template' => '###-####'],
            ['name' => 'Argentina', 'code' => 'AR', 'flag' => '🇦🇷', 'phone_code' => '+54', 'phone_template' => '### ### ####'],
            ['name' => 'Armenia', 'code' => 'AM', 'flag' => '🇦🇲', 'phone_code' => '+374', 'phone_template' => '## ######'],
            ['name' => 'Australia', 'code' => 'AU', 'flag' => '🇦🇺', 'phone_code' => '+61', 'phone_template' => '#### ### ###'],
            ['name' => 'Austria', 'code' => 'AT', 'flag' => '🇦🇹', 'phone_code' => '+43', 'phone_template' => '### ### ####'],
            ['name' => 'Azerbaijan', 'code' => 'AZ', 'flag' => '🇦🇿', 'phone_code' => '+994', 'phone_template' => '## ### ## ##'],

            // B
            ['name' => 'Bahamas', 'code' => 'BS', 'flag' => '🇧🇸', 'phone_code' => '+1242', 'phone_template' => '###-####'],
            ['name' => 'Bahrain', 'code' => 'BH', 'flag' => '🇧🇭', 'phone_code' => '+973', 'phone_template' => '#### ####'],
            ['name' => 'Bangladesh', 'code' => 'BD', 'flag' => '🇧🇩', 'phone_code' => '+880', 'phone_template' => '####-######'],
            ['name' => 'Barbados', 'code' => 'BB', 'flag' => '🇧🇧', 'phone_code' => '+1246', 'phone_template' => '###-####'],
            ['name' => 'Belarus', 'code' => 'BY', 'flag' => '🇧🇾', 'phone_code' => '+375', 'phone_template' => '## ###-##-##'],
            ['name' => 'Belgium', 'code' => 'BE', 'flag' => '🇧🇪', 'phone_code' => '+32', 'phone_template' => '### ## ## ##'],
            ['name' => 'Belize', 'code' => 'BZ', 'flag' => '🇧🇿', 'phone_code' => '+501', 'phone_template' => '###-####'],
            ['name' => 'Benin', 'code' => 'BJ', 'flag' => '🇧🇯', 'phone_code' => '+229', 'phone_template' => '## ## ## ##'],
            ['name' => 'Bhutan', 'code' => 'BT', 'flag' => '🇧🇹', 'phone_code' => '+975', 'phone_template' => '## ### ###'],
            ['name' => 'Bolivia', 'code' => 'BO', 'flag' => '🇧🇴', 'phone_code' => '+591', 'phone_template' => '########'],
            ['name' => 'Bosnia and Herzegovina', 'code' => 'BA', 'flag' => '🇧🇦', 'phone_code' => '+387', 'phone_template' => '##-###-###'],
            ['name' => 'Botswana', 'code' => 'BW', 'flag' => '🇧🇼', 'phone_code' => '+267', 'phone_template' => '## ### ###'],
            ['name' => 'Brazil', 'code' => 'BR', 'flag' => '🇧🇷', 'phone_code' => '+55', 'phone_template' => '(##) #####-####'],
            ['name' => 'Brunei', 'code' => 'BN', 'flag' => '🇧🇳', 'phone_code' => '+673', 'phone_template' => '### ####'],
            ['name' => 'Bulgaria', 'code' => 'BG', 'flag' => '🇧🇬', 'phone_code' => '+359', 'phone_template' => '## ### ####'],
            ['name' => 'Burkina Faso', 'code' => 'BF', 'flag' => '🇧🇫', 'phone_code' => '+226', 'phone_template' => '## ## ## ##'],
            ['name' => 'Burundi', 'code' => 'BI', 'flag' => '🇧🇮', 'phone_code' => '+257', 'phone_template' => '## ## ## ##'],

            // C
            ['name' => 'Cabo Verde', 'code' => 'CV', 'flag' => '🇨🇻', 'phone_code' => '+238', 'phone_template' => '### ## ##'],
            ['name' => 'Cambodia', 'code' => 'KH', 'flag' => '🇰🇭', 'phone_code' => '+855', 'phone_template' => '## ### ###'],
            ['name' => 'Cameroon', 'code' => 'CM', 'flag' => '🇨🇲', 'phone_code' => '+237', 'phone_template' => '## ## ## ##'],
            ['name' => 'Canada', 'code' => 'CA', 'flag' => '🇨🇦', 'phone_code' => '+1', 'phone_template' => '(###) ###-####'],
            ['name' => 'Central African Republic', 'code' => 'CF', 'flag' => '🇨🇫', 'phone_code' => '+236', 'phone_template' => '## ## ## ##'],
            ['name' => 'Chad', 'code' => 'TD', 'flag' => '🇹🇩', 'phone_code' => '+235', 'phone_template' => '## ## ## ##'],
            ['name' => 'Chile', 'code' => 'CL', 'flag' => '🇨🇱', 'phone_code' => '+56', 'phone_template' => '# #### ####'],
            ['name' => 'China', 'code' => 'CN', 'flag' => '🇨🇳', 'phone_code' => '+86', 'phone_template' => '### #### ####'],
            ['name' => 'Colombia', 'code' => 'CO', 'flag' => '🇨🇴', 'phone_code' => '+57', 'phone_template' => '### ### ####'],
            ['name' => 'Comoros', 'code' => 'KM', 'flag' => '🇰🇲', 'phone_code' => '+269', 'phone_template' => '## ## ###'],
            ['name' => 'Congo', 'code' => 'CG', 'flag' => '🇨🇬', 'phone_code' => '+242', 'phone_template' => '## ### ####'],
            ['name' => 'Congo (Democratic Republic)', 'code' => 'CD', 'flag' => '🇨🇩', 'phone_code' => '+243', 'phone_template' => '### ### ###'],
            ['name' => 'Costa Rica', 'code' => 'CR', 'flag' => '🇨🇷', 'phone_code' => '+506', 'phone_template' => '#### ####'],
            ['name' => 'Croatia', 'code' => 'HR', 'flag' => '🇭🇷', 'phone_code' => '+385', 'phone_template' => '##-###-####'],
            ['name' => 'Cuba', 'code' => 'CU', 'flag' => '🇨🇺', 'phone_code' => '+53', 'phone_template' => '########'],
            ['name' => 'Cyprus', 'code' => 'CY', 'flag' => '🇨🇾', 'phone_code' => '+357', 'phone_template' => '## ######'],
            ['name' => 'Czech Republic', 'code' => 'CZ', 'flag' => '🇨🇿', 'phone_code' => '+420', 'phone_template' => '### ### ###'],

            // D
            ['name' => 'Denmark', 'code' => 'DK', 'flag' => '🇩🇰', 'phone_code' => '+45', 'phone_template' => '## ## ## ##'],
            ['name' => 'Djibouti', 'code' => 'DJ', 'flag' => '🇩🇯', 'phone_code' => '+253', 'phone_template' => '## ## ## ##'],
            ['name' => 'Dominica', 'code' => 'DM', 'flag' => '🇩🇲', 'phone_code' => '+1767', 'phone_template' => '###-####'],
            ['name' => 'Dominican Republic', 'code' => 'DO', 'flag' => '🇩🇴', 'phone_code' => '+1809', 'phone_template' => '###-####'],

            // E
            ['name' => 'Ecuador', 'code' => 'EC', 'flag' => '🇪🇨', 'phone_code' => '+593', 'phone_template' => '##-###-####'],
            ['name' => 'Egypt', 'code' => 'EG', 'flag' => '🇪🇬', 'phone_code' => '+20', 'phone_template' => '### ### ####'],
            ['name' => 'El Salvador', 'code' => 'SV', 'flag' => '🇸🇻', 'phone_code' => '+503', 'phone_template' => '#### ####'],
            ['name' => 'Equatorial Guinea', 'code' => 'GQ', 'flag' => '🇬🇶', 'phone_code' => '+240', 'phone_template' => '## ### ####'],
            ['name' => 'Eritrea', 'code' => 'ER', 'flag' => '🇪🇷', 'phone_code' => '+291', 'phone_template' => '# ### ###'],
            ['name' => 'Estonia', 'code' => 'EE', 'flag' => '🇪🇪', 'phone_code' => '+372', 'phone_template' => '#### ####'],
            ['name' => 'Eswatini', 'code' => 'SZ', 'flag' => '🇸🇿', 'phone_code' => '+268', 'phone_template' => '## ## ## ##'],
            ['name' => 'Ethiopia', 'code' => 'ET', 'flag' => '🇪🇹', 'phone_code' => '+251', 'phone_template' => '## ### ####'],

            // F
            ['name' => 'Fiji', 'code' => 'FJ', 'flag' => '🇫🇯', 'phone_code' => '+679', 'phone_template' => '### ####'],
            ['name' => 'Finland', 'code' => 'FI', 'flag' => '🇫🇮', 'phone_code' => '+358', 'phone_template' => '## ### ####'],
            ['name' => 'France', 'code' => 'FR', 'flag' => '🇫🇷', 'phone_code' => '+33', 'phone_template' => '## ## ## ## ##'],

            // G
            ['name' => 'Gabon', 'code' => 'GA', 'flag' => '🇬🇦', 'phone_code' => '+241', 'phone_template' => '## ## ## ##'],
            ['name' => 'Gambia', 'code' => 'GM', 'flag' => '🇬🇲', 'phone_code' => '+220', 'phone_template' => '### ####'],
            ['name' => 'Georgia', 'code' => 'GE', 'flag' => '🇬🇪', 'phone_code' => '+995', 'phone_template' => '### ### ###'],
            ['name' => 'Germany', 'code' => 'DE', 'flag' => '🇩🇪', 'phone_code' => '+49', 'phone_template' => '### ### ####'],
            ['name' => 'Ghana', 'code' => 'GH', 'flag' => '🇬🇭', 'phone_code' => '+233', 'phone_template' => '### ### ###'],
            ['name' => 'Greece', 'code' => 'GR', 'flag' => '🇬🇷', 'phone_code' => '+30', 'phone_template' => '### ### ####'],
            ['name' => 'Grenada', 'code' => 'GD', 'flag' => '🇬🇩', 'phone_code' => '+1473', 'phone_template' => '###-####'],
            ['name' => 'Guatemala', 'code' => 'GT', 'flag' => '🇬🇹', 'phone_code' => '+502', 'phone_template' => '#### ####'],
            ['name' => 'Guinea', 'code' => 'GN', 'flag' => '🇬🇳', 'phone_code' => '+224', 'phone_template' => '## ## ## ##'],
            ['name' => 'Guinea-Bissau', 'code' => 'GW', 'flag' => '🇬🇼', 'phone_code' => '+245', 'phone_template' => '### ####'],
            ['name' => 'Guyana', 'code' => 'GY', 'flag' => '🇬🇾', 'phone_code' => '+592', 'phone_template' => '### ####'],

            // H
            ['name' => 'Haiti', 'code' => 'HT', 'flag' => '🇭🇹', 'phone_code' => '+509', 'phone_template' => '## ## ####'],
            ['name' => 'Honduras', 'code' => 'HN', 'flag' => '🇭🇳', 'phone_code' => '+504', 'phone_template' => '#### ####'],
            ['name' => 'Hungary', 'code' => 'HU', 'flag' => '🇭🇺', 'phone_code' => '+36', 'phone_template' => '### ### ###'],

            // I
            ['name' => 'Iceland', 'code' => 'IS', 'flag' => '🇮🇸', 'phone_code' => '+354', 'phone_template' => '### ####'],
            ['name' => 'India', 'code' => 'IN', 'flag' => '🇮🇳', 'phone_code' => '+91', 'phone_template' => '##### #####'],
            ['name' => 'Indonesia', 'code' => 'ID', 'flag' => '🇮🇩', 'phone_code' => '+62', 'phone_template' => '###-###-####'],
            ['name' => 'Iran', 'code' => 'IR', 'flag' => '🇮🇷', 'phone_code' => '+98', 'phone_template' => '### ### ####'],
            ['name' => 'Iraq', 'code' => 'IQ', 'flag' => '🇮🇶', 'phone_code' => '+964', 'phone_template' => '### ### ####'],
            ['name' => 'Ireland', 'code' => 'IE', 'flag' => '🇮🇪', 'phone_code' => '+353', 'phone_template' => '## ### ####'],
            ['name' => 'Israel', 'code' => 'IL', 'flag' => '🇮🇱', 'phone_code' => '+972', 'phone_template' => '##-###-####'],
            ['name' => 'Italy', 'code' => 'IT', 'flag' => '🇮🇹', 'phone_code' => '+39', 'phone_template' => '### ### ####'],
            ['name' => 'Ivory Coast', 'code' => 'CI', 'flag' => '🇨🇮', 'phone_code' => '+225', 'phone_template' => '## ## ## ##'],

            // J
            ['name' => 'Jamaica', 'code' => 'JM', 'flag' => '🇯🇲', 'phone_code' => '+1876', 'phone_template' => '###-####'],
            ['name' => 'Japan', 'code' => 'JP', 'flag' => '🇯🇵', 'phone_code' => '+81', 'phone_template' => '###-####-####'],
            ['name' => 'Jordan', 'code' => 'JO', 'flag' => '🇯🇴', 'phone_code' => '+962', 'phone_template' => '# #### ####'],

            // K
            ['name' => 'Kazakhstan', 'code' => 'KZ', 'flag' => '🇰🇿', 'phone_code' => '+7', 'phone_template' => '### ###-##-##'],
            ['name' => 'Kenya', 'code' => 'KE', 'flag' => '🇰🇪', 'phone_code' => '+254', 'phone_template' => '### ### ###'],
            ['name' => 'Kiribati', 'code' => 'KI', 'flag' => '🇰🇮', 'phone_code' => '+686', 'phone_template' => '## ###'],
            ['name' => 'North Korea', 'code' => 'KP', 'flag' => '🇰🇵', 'phone_code' => '+850', 'phone_template' => '### ### ####'],
            ['name' => 'South Korea', 'code' => 'KR', 'flag' => '🇰🇷', 'phone_code' => '+82', 'phone_template' => '###-####-####'],
            ['name' => 'Kuwait', 'code' => 'KW', 'flag' => '🇰🇼', 'phone_code' => '+965', 'phone_template' => '#### ####'],
            ['name' => 'Kyrgyzstan', 'code' => 'KG', 'flag' => '🇰🇬', 'phone_code' => '+996', 'phone_template' => '### ### ###'],

            // L
            ['name' => 'Laos', 'code' => 'LA', 'flag' => '🇱🇦', 'phone_code' => '+856', 'phone_template' => '## ### ###'],
            ['name' => 'Latvia', 'code' => 'LV', 'flag' => '🇱🇻', 'phone_code' => '+371', 'phone_template' => '## ### ###'],
            ['name' => 'Lebanon', 'code' => 'LB', 'flag' => '🇱🇧', 'phone_code' => '+961', 'phone_template' => '## ### ###'],
            ['name' => 'Lesotho', 'code' => 'LS', 'flag' => '🇱🇸', 'phone_code' => '+266', 'phone_template' => '## ### ###'],
            ['name' => 'Liberia', 'code' => 'LR', 'flag' => '🇱🇷', 'phone_code' => '+231', 'phone_template' => '## ### ###'],
            ['name' => 'Libya', 'code' => 'LY', 'flag' => '🇱🇾', 'phone_code' => '+218', 'phone_template' => '##-#######'],
            ['name' => 'Liechtenstein', 'code' => 'LI', 'flag' => '🇱🇮', 'phone_code' => '+423', 'phone_template' => '### ####'],
            ['name' => 'Lithuania', 'code' => 'LT', 'flag' => '🇱🇹', 'phone_code' => '+370', 'phone_template' => '### ## ###'],
            ['name' => 'Luxembourg', 'code' => 'LU', 'flag' => '🇱🇺', 'phone_code' => '+352', 'phone_template' => '### ### ###'],

            // M
            ['name' => 'Madagascar', 'code' => 'MG', 'flag' => '🇲🇬', 'phone_code' => '+261', 'phone_template' => '## ## ### ##'],
            ['name' => 'Malawi', 'code' => 'MW', 'flag' => '🇲🇼', 'phone_code' => '+265', 'phone_template' => '## ### ####'],
            ['name' => 'Malaysia', 'code' => 'MY', 'flag' => '🇲🇾', 'phone_code' => '+60', 'phone_template' => '##-### ####'],
            ['name' => 'Maldives', 'code' => 'MV', 'flag' => '🇲🇻', 'phone_code' => '+960', 'phone_template' => '###-####'],
            ['name' => 'Mali', 'code' => 'ML', 'flag' => '🇲🇱', 'phone_code' => '+223', 'phone_template' => '## ## ## ##'],
            ['name' => 'Malta', 'code' => 'MT', 'flag' => '🇲🇹', 'phone_code' => '+356', 'phone_template' => '#### ####'],
            ['name' => 'Marshall Islands', 'code' => 'MH', 'flag' => '🇲🇭', 'phone_code' => '+692', 'phone_template' => '###-####'],
            ['name' => 'Mauritania', 'code' => 'MR', 'flag' => '🇲🇷', 'phone_code' => '+222', 'phone_template' => '## ## ## ##'],
            ['name' => 'Mauritius', 'code' => 'MU', 'flag' => '🇲🇺', 'phone_code' => '+230', 'phone_template' => '#### ####'],
            ['name' => 'Mexico', 'code' => 'MX', 'flag' => '🇲🇽', 'phone_code' => '+52', 'phone_template' => '### ### ####'],
            ['name' => 'Micronesia', 'code' => 'FM', 'flag' => '🇫🇲', 'phone_code' => '+691', 'phone_template' => '###-####'],
            ['name' => 'Moldova', 'code' => 'MD', 'flag' => '🇲🇩', 'phone_code' => '+373', 'phone_template' => '## ### ###'],
            ['name' => 'Monaco', 'code' => 'MC', 'flag' => '🇲🇨', 'phone_code' => '+377', 'phone_template' => '## ## ## ##'],
            ['name' => 'Mongolia', 'code' => 'MN', 'flag' => '🇲🇳', 'phone_code' => '+976', 'phone_template' => '#### ####'],
            ['name' => 'Montenegro', 'code' => 'ME', 'flag' => '🇲🇪', 'phone_code' => '+382', 'phone_template' => '## ### ###'],
            ['name' => 'Morocco', 'code' => 'MA', 'flag' => '🇲🇦', 'phone_code' => '+212', 'phone_template' => '###-###-###'],
            ['name' => 'Mozambique', 'code' => 'MZ', 'flag' => '🇲🇿', 'phone_code' => '+258', 'phone_template' => '## ### ####'],
            ['name' => 'Myanmar', 'code' => 'MM', 'flag' => '🇲🇲', 'phone_code' => '+95', 'phone_template' => '###-###-####'],

            // N
            ['name' => 'Namibia', 'code' => 'NA', 'flag' => '🇳🇦', 'phone_code' => '+264', 'phone_template' => '## ### ####'],
            ['name' => 'Nauru', 'code' => 'NR', 'flag' => '🇳🇷', 'phone_code' => '+674', 'phone_template' => '### ####'],
            ['name' => 'Nepal', 'code' => 'NP', 'flag' => '🇳🇵', 'phone_code' => '+977', 'phone_template' => '###-#######'],
            ['name' => 'Netherlands', 'code' => 'NL', 'flag' => '🇳🇱', 'phone_code' => '+31', 'phone_template' => '## ### ####'],
            ['name' => 'New Zealand', 'code' => 'NZ', 'flag' => '🇳🇿', 'phone_code' => '+64', 'phone_template' => '##-### ####'],
            ['name' => 'Nicaragua', 'code' => 'NI', 'flag' => '🇳🇮', 'phone_code' => '+505', 'phone_template' => '#### ####'],
            ['name' => 'Niger', 'code' => 'NE', 'flag' => '🇳🇪', 'phone_code' => '+227', 'phone_template' => '## ## ## ##'],
            ['name' => 'Nigeria', 'code' => 'NG', 'flag' => '🇳🇬', 'phone_code' => '+234', 'phone_template' => '### ### ####'],
            ['name' => 'North Macedonia', 'code' => 'MK', 'flag' => '🇲🇰', 'phone_code' => '+389', 'phone_template' => '## ### ###'],
            ['name' => 'Norway', 'code' => 'NO', 'flag' => '🇳🇴', 'phone_code' => '+47', 'phone_template' => '### ## ###'],

            // O
            ['name' => 'Oman', 'code' => 'OM', 'flag' => '🇴🇲', 'phone_code' => '+968', 'phone_template' => '#### ####'],

            // P
            ['name' => 'Pakistan', 'code' => 'PK', 'flag' => '🇵🇰', 'phone_code' => '+92', 'phone_template' => '### ### ####'],
            ['name' => 'Palau', 'code' => 'PW', 'flag' => '🇵🇼', 'phone_code' => '+680', 'phone_template' => '###-####'],
            ['name' => 'Palestine', 'code' => 'PS', 'flag' => '🇵🇸', 'phone_code' => '+970', 'phone_template' => '### ### ###'],
            ['name' => 'Panama', 'code' => 'PA', 'flag' => '🇵🇦', 'phone_code' => '+507', 'phone_template' => '#### ####'],
            ['name' => 'Papua New Guinea', 'code' => 'PG', 'flag' => '🇵🇬', 'phone_code' => '+675', 'phone_template' => '### ####'],
            ['name' => 'Paraguay', 'code' => 'PY', 'flag' => '🇵🇾', 'phone_code' => '+595', 'phone_template' => '### ### ###'],
            ['name' => 'Peru', 'code' => 'PE', 'flag' => '🇵🇪', 'phone_code' => '+51', 'phone_template' => '### ### ###'],
            ['name' => 'Philippines', 'code' => 'PH', 'flag' => '🇵🇭', 'phone_code' => '+63', 'phone_template' => '### ### ####'],
            ['name' => 'Poland', 'code' => 'PL', 'flag' => '🇵🇱', 'phone_code' => '+48', 'phone_template' => '### ### ###'],
            ['name' => 'Portugal', 'code' => 'PT', 'flag' => '🇵🇹', 'phone_code' => '+351', 'phone_template' => '### ### ###'],

            // Q
            ['name' => 'Qatar', 'code' => 'QA', 'flag' => '🇶🇦', 'phone_code' => '+974', 'phone_template' => '#### ####'],

            // R
            ['name' => 'Romania', 'code' => 'RO', 'flag' => '🇷🇴', 'phone_code' => '+40', 'phone_template' => '### ### ###'],
            ['name' => 'Russia', 'code' => 'RU', 'flag' => '🇷🇺', 'phone_code' => '+7', 'phone_template' => '### ###-##-##'],
            ['name' => 'Rwanda', 'code' => 'RW', 'flag' => '🇷🇼', 'phone_code' => '+250', 'phone_template' => '### ### ###'],

            // S
            ['name' => 'Saint Kitts and Nevis', 'code' => 'KN', 'flag' => '🇰🇳', 'phone_code' => '+1869', 'phone_template' => '###-####'],
            ['name' => 'Saint Lucia', 'code' => 'LC', 'flag' => '🇱🇨', 'phone_code' => '+1758', 'phone_template' => '###-####'],
            ['name' => 'Saint Vincent and the Grenadines', 'code' => 'VC', 'flag' => '🇻🇨', 'phone_code' => '+1784', 'phone_template' => '###-####'],
            ['name' => 'Samoa', 'code' => 'WS', 'flag' => '🇼🇸', 'phone_code' => '+685', 'phone_template' => '## ####'],
            ['name' => 'San Marino', 'code' => 'SM', 'flag' => '🇸🇲', 'phone_code' => '+378', 'phone_template' => '#### ######'],
            ['name' => 'Sao Tome and Principe', 'code' => 'ST', 'flag' => '🇸🇹', 'phone_code' => '+239', 'phone_template' => '## #####'],
            ['name' => 'Saudi Arabia', 'code' => 'SA', 'flag' => '🇸🇦', 'phone_code' => '+966', 'phone_template' => '## ### ####'],
            ['name' => 'Senegal', 'code' => 'SN', 'flag' => '🇸🇳', 'phone_code' => '+221', 'phone_template' => '## ### ## ##'],
            ['name' => 'Serbia', 'code' => 'RS', 'flag' => '🇷🇸', 'phone_code' => '+381', 'phone_template' => '## ### ####'],
            ['name' => 'Seychelles', 'code' => 'SC', 'flag' => '🇸🇨', 'phone_code' => '+248', 'phone_template' => '# ### ###'],
            ['name' => 'Sierra Leone', 'code' => 'SL', 'flag' => '🇸🇱', 'phone_code' => '+232', 'phone_template' => '## ######'],
            ['name' => 'Singapore', 'code' => 'SG', 'flag' => '🇸🇬', 'phone_code' => '+65', 'phone_template' => '#### ####'],
            ['name' => 'Slovakia', 'code' => 'SK', 'flag' => '🇸🇰', 'phone_code' => '+421', 'phone_template' => '### ### ###'],
            ['name' => 'Slovenia', 'code' => 'SI', 'flag' => '🇸🇮', 'phone_code' => '+386', 'phone_template' => '## ### ###'],
            ['name' => 'Solomon Islands', 'code' => 'SB', 'flag' => '🇸🇧', 'phone_code' => '+677', 'phone_template' => '## ###'],
            ['name' => 'Somalia', 'code' => 'SO', 'flag' => '🇸🇴', 'phone_code' => '+252', 'phone_template' => '## ### ###'],
            ['name' => 'South Africa', 'code' => 'ZA', 'flag' => '🇿🇦', 'phone_code' => '+27', 'phone_template' => '## ### ####'],
            ['name' => 'South Sudan', 'code' => 'SS', 'flag' => '🇸🇸', 'phone_code' => '+211', 'phone_template' => '### ### ###'],
            ['name' => 'Spain', 'code' => 'ES', 'flag' => '🇪🇸', 'phone_code' => '+34', 'phone_template' => '### ### ###'],
            ['name' => 'Sri Lanka', 'code' => 'LK', 'flag' => '🇱🇰', 'phone_code' => '+94', 'phone_template' => '## ### ####'],
            ['name' => 'Sudan', 'code' => 'SD', 'flag' => '🇸🇩', 'phone_code' => '+249', 'phone_template' => '### ### ###'],
            ['name' => 'Suriname', 'code' => 'SR', 'flag' => '🇸🇷', 'phone_code' => '+597', 'phone_template' => '###-####'],
            ['name' => 'Sweden', 'code' => 'SE', 'flag' => '🇸🇪', 'phone_code' => '+46', 'phone_template' => '## ### ## ##'],
            ['name' => 'Switzerland', 'code' => 'CH', 'flag' => '🇨🇭', 'phone_code' => '+41', 'phone_template' => '## ### ## ##'],
            ['name' => 'Syria', 'code' => 'SY', 'flag' => '🇸🇾', 'phone_code' => '+963', 'phone_template' => '### ### ###'],

            // T
            ['name' => 'Tajikistan', 'code' => 'TJ', 'flag' => '🇹🇯', 'phone_code' => '+992', 'phone_template' => '### ### ###'],
            ['name' => 'Tanzania', 'code' => 'TZ', 'flag' => '🇹🇿', 'phone_code' => '+255', 'phone_template' => '### ### ###'],
            ['name' => 'Thailand', 'code' => 'TH', 'flag' => '🇹🇭', 'phone_code' => '+66', 'phone_template' => '##-###-####'],
            ['name' => 'Timor-Leste', 'code' => 'TL', 'flag' => '🇹🇱', 'phone_code' => '+670', 'phone_template' => '### ####'],
            ['name' => 'Togo', 'code' => 'TG', 'flag' => '🇹🇬', 'phone_code' => '+228', 'phone_template' => '## ## ## ##'],
            ['name' => 'Tonga', 'code' => 'TO', 'flag' => '🇹🇴', 'phone_code' => '+676', 'phone_template' => '## ###'],
            ['name' => 'Trinidad and Tobago', 'code' => 'TT', 'flag' => '🇹🇹', 'phone_code' => '+1868', 'phone_template' => '###-####'],
            ['name' => 'Tunisia', 'code' => 'TN', 'flag' => '🇹🇳', 'phone_code' => '+216', 'phone_template' => '## ### ###'],
            ['name' => 'Turkey', 'code' => 'TR', 'flag' => '🇹🇷', 'phone_code' => '+90', 'phone_template' => '### ### ## ##'],
            ['name' => 'Turkmenistan', 'code' => 'TM', 'flag' => '🇹🇲', 'phone_code' => '+993', 'phone_template' => '## ######'],
            ['name' => 'Tuvalu', 'code' => 'TV', 'flag' => '🇹🇻', 'phone_code' => '+688', 'phone_template' => '## ###'],

            // U
            ['name' => 'Uganda', 'code' => 'UG', 'flag' => '🇺🇬', 'phone_code' => '+256', 'phone_template' => '### ### ###'],
            ['name' => 'Ukraine', 'code' => 'UA', 'flag' => '🇺🇦', 'phone_code' => '+380', 'phone_template' => '## ### ## ##'],
            ['name' => 'United Arab Emirates', 'code' => 'AE', 'flag' => '🇦🇪', 'phone_code' => '+971', 'phone_template' => '## ### ####'],
            ['name' => 'United Kingdom', 'code' => 'GB', 'flag' => '🇬🇧', 'phone_code' => '+44', 'phone_template' => '#### ### ####'],
            ['name' => 'United States', 'code' => 'US', 'flag' => '🇺🇸', 'phone_code' => '+1', 'phone_template' => '(###) ###-####'],
            ['name' => 'Uruguay', 'code' => 'UY', 'flag' => '🇺🇾', 'phone_code' => '+598', 'phone_template' => '#### ####'],
            ['name' => 'Uzbekistan', 'code' => 'UZ', 'flag' => '🇺🇿', 'phone_code' => '+998', 'phone_template' => '## ### ## ##'],

            // V
            ['name' => 'Vanuatu', 'code' => 'VU', 'flag' => '🇻🇺', 'phone_code' => '+678', 'phone_template' => '## ###'],
            ['name' => 'Vatican City', 'code' => 'VA', 'flag' => '🇻🇦', 'phone_code' => '+379', 'phone_template' => '## ## ## ##'],
            ['name' => 'Venezuela', 'code' => 'VE', 'flag' => '🇻🇪', 'phone_code' => '+58', 'phone_template' => '###-#######'],
            ['name' => 'Vietnam', 'code' => 'VN', 'flag' => '🇻🇳', 'phone_code' => '+84', 'phone_template' => '### ### ####'],

            // Y
            ['name' => 'Yemen', 'code' => 'YE', 'flag' => '🇾🇪', 'phone_code' => '+967', 'phone_template' => '### ### ###'],

            // Z
            ['name' => 'Zambia', 'code' => 'ZM', 'flag' => '🇿🇲', 'phone_code' => '+260', 'phone_template' => '## ### ####'],
            ['name' => 'Zimbabwe', 'code' => 'ZW', 'flag' => '🇿🇼', 'phone_code' => '+263', 'phone_template' => '## ### ####']
        ];

        foreach ($countries as $country) {
            Country::create($country);
        }
    }
}

