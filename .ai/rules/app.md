---
paths:
  - 'app/**/Post*.php'
  - 'app/**'
---

# App

## Pair sourced posts with review dates
Official source and last-reviewed fields are optional for evergreen posts. Policy or planning posts opt into tracking by setting both fields; sourced posts are flagged for review after 180 days. Public pages should link the official source and expose the citation and review date in structured data.

## Create dates through Laravel's Date facade
Use Illuminate\Support\Facades\Date for application date creation (Date::now(), Date::today(), Date::parse()) rather than global date helpers or direct Carbon static creation. Keep accurate Carbon/CarbonInterface object type declarations; Date is a facade, not an object type. Preserve current mutable-date behavior; do not configure immutability or convert objects merely to satisfy analysis.
