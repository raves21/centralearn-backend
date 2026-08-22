# Settings Documentation: Accessibility & Submission Settings

---

## `accessibility_settings`

Stored as a **JSON column** on the `chapter_contents` table. Controls whether a student can see or open a piece of content.

### Shape

```json
// Option A — explicit visibility
{
  "visible": true | false,
  "custom": null
}

// Option B — time-bounded access window
{
  "visible": null,
  "custom": {
    "access_from": "2026-03-26 00:00:00",
    "access_until": "2026-03-30 00:00:00"
  }
}

// Option C — open-ended access from a start date
{
  "visible": null,
  "custom": {
    "access_from": "2026-03-26 00:00:00",
    "access_until": null
  }
}
```

### Modes

**`visible: true`**
The content is always accessible to students. No time restrictions apply.

**`visible: false`**
The content is hidden from students entirely.

**`custom`**
Access is restricted to a defined time window. `access_from` marks when the content becomes accessible, and `access_until` marks when access ends.

- `access_from` is required.
- `access_until` is optional. If `null`, access is open-ended starting from `access_from`.

### Rules

- `visible` and `custom` are mutually exclusive. If one has a value, the other must be `null`.
- If `accessibility_settings` itself is `null`, the content is treated as **hidden** (`visible: false`) by default.
- All timestamps are stored and interpreted as **UTC**.

---

## `assessment_submission_settings`

Stored as a **dedicated table**, related to `assessments` via a `hasOne` relationship (`Assessment::submissionSettings()`). Controls time limits, due dates, and attempt behavior relative to the due date.

### Table Structure

```php
Schema::create('assessment_submission_settings', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('assessment_id')->constrained()->cascadeOnDelete();
    $table->timestamp('due_date')->nullable();
    $table->enum('after_due_date_behavior', ['auto_submit', 'block_new_attempts', 'allow_all'])->nullable();
    $table->integer('time_limit_seconds');
    $table->timestamps();
});
```

### Relationships

```php
class Assessment extends Model
{
    public function submissionSettings()
    {
        return $this->hasOne(AssessmentSubmissionSettings::class);
    }
}

class AssessmentSubmissionSettings extends Model
{
    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }
}
```

`submissionSettings` is eagerly loaded by default on `Assessment` via `$with`, so accessing `$assessment->submissionSettings` does not trigger an additional query in most cases. When loading attempts, eager-load the full chain explicitly: `with('assessmentVersion.assessment.submissionSettings')`.

### Fields

**`time_limit_seconds`**
The maximum time a student is allowed to spend on a single attempt, in seconds (e.g. `300` = 5 minutes). Required, but can functionally represent "no limit" with a very large value if needed — confirm against your validation rules whether `null` is permitted here, since the column itself is **not nullable**.

**`due_date`**
The deadline for submissions. If `null`, there is no deadline and `after_due_date_behavior` must also be `null`.

**`after_due_date_behavior`**
Defines what happens to ongoing attempts and new attempts once the `due_date` is reached.

| Value                  | Ongoing Attempts                   | New Attempts |
| ---------------------- | ---------------------------------- | ------------ |
| `"auto_submit"`        | Force-submitted at the due date    | Blocked      |
| `"block_new_attempts"` | May be completed past the due date | Blocked      |
| `"allow_all"`          | May be completed past the due date | Allowed      |

### Rules

- If `due_date` is `null`, `after_due_date_behavior` must be `null`.
- If `due_date` is set, `after_due_date_behavior` must have a valid value.
- All timestamps are stored and interpreted as **UTC**.
- Each `Assessment` has exactly one `AssessmentSubmissionSettings` record (`hasOne` / `belongsTo`).

---

## Interaction Between `accessibility_settings` and `assessment_submission_settings`

These two settings govern separate concerns and operate independently.

- `accessibility_settings` determines whether a student can **see or open** the content.
- `assessment_submission_settings` determines **submission deadlines and attempt behavior** once the student is inside the assessment.

A student cannot open an assessment unless `accessibility_settings` permits access, regardless of what `assessment_submission_settings` says. Once inside, `due_date` and `after_due_date_behavior` take over to govern submission behavior.

`access_until` and `due_date` can differ. For example, if `access_until` is later than `due_date`, students can still open the assessment after the due date — but their attempt behavior will be governed by `after_due_date_behavior`.

**Example:**

| Setting                                                              | Value                  |
| -------------------------------------------------------------------- | ---------------------- |
| `accessibility_settings.custom.access_until` (on `chapter_contents`) | March 30, 2026         |
| `assessment_submission_settings.due_date`                            | March 28, 2026         |
| `assessment_submission_settings.after_due_date_behavior`             | `"block_new_attempts"` |

Students can open the assessment until March 30, but cannot start new attempts after March 28. Any attempt already in progress at the due date may still be completed.
