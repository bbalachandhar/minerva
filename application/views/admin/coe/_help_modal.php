<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
/**
 * Shared CoE Help Modal
 * Usage: $this->load->view('admin/coe/_help_modal', ['help_key' => 'nominal_roll']);
 * Drop an info button anywhere: <button class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button>
 */

$helps = [
    'exam_regulations' => [
        'title' => 'Exam Regulations',
        'icon'  => 'fa-university',
        'color' => '#1565c0',
        'text'  => '
            <p><strong>Exam Regulations</strong> define the ruleset that governs an entire examination cycle for a programme/batch.</p>
            <ul>
                <li><strong>Attendance %</strong> — minimum attendance required to sit the exam (typically 75%).</li>
                <li><strong>Internal marks</strong> — minimum CIA/internal marks required for eligibility.</li>
                <li><strong>Passing criteria</strong> — external pass mark (e.g., 28/70), total pass mark (e.g., 50/100).</li>
                <li><strong>Grading scale</strong> — NEP2020 Anna University scale (O ≥ 91 = 10, A+ ≥ 81 = 9 … U &lt; 50 = 0).</li>
                <li><strong>Grace policy</strong> — maximum moderation/grace marks allowed per subject.</li>
            </ul>
            <p class="text-info"><i class="fa fa-lightbulb-o"></i> Regulations must be created <em>before</em> opening exam applications. All downstream calculations (eligibility, hall tickets, marks, results) follow these rules.</p>
        ',
    ],
    'exam_events' => [
        'title' => 'Exam Events (Applications)',
        'icon'  => 'fa-calendar-check-o',
        'color' => '#2e7d32',
        'text'  => '
            <p><strong>Exam Events</strong> (also called Exam Applications or Batch Exams) represent a specific examination period — e.g., <em>Nov/Dec 2025 – CSE Semester 3</em>.</p>
            <ul>
                <li>Create an event to open the exam window for a batch/semester.</li>
                <li>Students (or staff on their behalf) apply for the subjects they wish to appear in.</li>
                <li>Applications carry an <strong>application_status</strong>: <em>pending → eligible / ineligible / override_eligible</em>.</li>
                <li>All other CoE modules (eligibility, hall tickets, nominal roll, seating, marks) are scoped to an exam event.</li>
            </ul>
            <p class="text-info"><i class="fa fa-lightbulb-o"></i> Think of this as "opening the exam window." Without an active exam event, no further CoE workflow can proceed.</p>
        ',
    ],
    'eligibility' => [
        'title' => 'Eligibility Verification',
        'icon'  => 'fa-check-circle',
        'color' => '#4527a0',
        'text'  => '
            <p><strong>Eligibility</strong> is the process of determining whether each student is permitted to sit the exam for each subject.</p>
            <ul>
                <li>System checks attendance % and internal marks against the Exam Regulations.</li>
                <li>Students meeting criteria are automatically marked <em>eligible</em>; others become <em>ineligible</em>.</li>
                <li><strong>Override</strong> — the CoE can manually mark an ineligible student as <em>override_eligible</em> with a reason (e.g., medical grounds, principal approval).</li>
                <li>Only <em>eligible</em> and <em>override_eligible</em> students proceed to hall ticket generation and nominal roll.</li>
            </ul>
            <p class="text-info"><i class="fa fa-lightbulb-o"></i> Review this carefully — any student marked ineligible here will NOT appear in nominal rolls or receive a hall ticket.</p>
        ',
    ],
    'hall_tickets' => [
        'title' => 'Hall Tickets (Admit Cards)',
        'icon'  => 'fa-id-card',
        'color' => '#1565c0',
        'text'  => '
            <p><strong>Hall Tickets</strong> (also called Admit Cards) are issued to eligible students to authorise entry into the exam hall.</p>
            <ul>
                <li>Generated automatically from the list of eligible/override_eligible students.</li>
                <li>Each ticket contains: student name, register number, photo, list of subjects, exam dates &amp; times, exam centre.</li>
                <li>Students must present the hall ticket at every exam session — invigilators verify it.</li>
                <li>Tickets can be printed individually or in bulk.</li>
            </ul>
            <p class="text-info"><i class="fa fa-lightbulb-o"></i> Hall tickets are a legal document. Ensure student photos and register numbers are correct before printing.</p>
        ',
    ],
    'nominal_roll' => [
        'title' => 'Nominal Roll',
        'icon'  => 'fa-list-ol',
        'color' => '#4527a0',
        'text'  => '
            <p><strong>The Nominal Roll</strong> is the official, per-subject register of students sitting for an exam, submitted to Anna University before exams begin.</p>
            <ul>
                <li>One roll is generated per subject — it contains all eligible students with their name, register number, class, and arrear status.</li>
                <li>It is the <strong>"approved attendance sheet"</strong> that the CoE sends to the university — essentially a commitment that these students are authorised to appear.</li>
                <li><strong>Generate</strong> — pulls eligible students from applications and creates a JSON snapshot at that point in time.</li>
                <li><strong>Finalize</strong> — locks the roll permanently. Finalized rolls cannot be regenerated. This is the submission point.</li>
                <li>Without a finalized nominal roll, results cannot be published for that subject.</li>
            </ul>
            <p class="text-info"><i class="fa fa-lightbulb-o"></i> Always verify the student count matches eligibility numbers before finalizing. Once finalized, adding/removing students requires a fresh application cycle.</p>
        ',
    ],
    'seating' => [
        'title' => 'Seating Arrangement',
        'icon'  => 'fa-th',
        'color' => '#1976d2',
        'text'  => '
            <p><strong>Seating Arrangement</strong> manages how eligible students are assigned to exam rooms and specific seats.</p>
            <ul>
                <li>Rooms are configured with their capacity and floor/block details.</li>
                <li>System assigns students ensuring <strong>cross-department interleaving</strong> (students from different departments seated alternately) as required by Anna University norms.</li>
                <li>A <strong>seating chart</strong> (room-wise list) can be printed and pasted outside each room.</li>
                <li>Seat numbers are passed to the attendance and answer script modules.</li>
            </ul>
            <p class="text-info"><i class="fa fa-lightbulb-o"></i> Generate seating only after all eligible students are confirmed. Changes to eligibility after seating is generated will require regenerating the plan.</p>
        ',
    ],
    'invigilation' => [
        'title' => 'Invigilation Duty Roster',
        'icon'  => 'fa-users',
        'color' => '#2e7d32',
        'text'  => '
            <p><strong>Invigilation</strong> assigns staff members as invigilators to exam rooms and sessions.</p>
            <ul>
                <li>Each room-session slot is assigned one or more invigilators based on capacity and norms.</li>
                <li>Staff duty is tracked to ensure equitable distribution and avoid conflicts.</li>
                <li>A <strong>Duty Roster</strong> (printable) is produced for distribution to staff.</li>
                <li>Invigilators are responsible for verifying hall tickets, recording attendance, and collecting answer scripts.</li>
            </ul>
            <p class="text-info"><i class="fa fa-lightbulb-o"></i> Coordinate with HR/admin to ensure assigned staff are available on exam dates. Share the printed roster with the Principal&apos;s office.</p>
        ',
    ],
    'qpd' => [
        'title' => 'Question Paper Distribution (QPD)',
        'icon'  => 'fa-file-text-o',
        'color' => '#e65100',
        'text'  => '
            <p><strong>Question Paper Distribution (QPD)</strong> manages the secure handling of question paper packets from receipt to room-level distribution.</p>
            <ul>
                <li>Packets are received from the university or printing press and logged (seal status, quantity).</li>
                <li>On exam day, packets are disbursed to rooms just before the exam starts.</li>
                <li>Tracks <em>who</em> opened the packet, <em>when</em>, and in front of <em>which witnesses</em> — essential for university audit.</li>
                <li>Any discrepancy (missing papers, early opening) is flagged immediately.</li>
            </ul>
            <p class="text-info"><i class="fa fa-lightbulb-o"></i> QPD is a security-critical workflow. Question papers must remain sealed until 15 minutes before the exam. All opening events must be logged here in real time.</p>
        ',
    ],
    'attendance' => [
        'title' => 'Exam Attendance',
        'icon'  => 'fa-check-square-o',
        'color' => '#1565c0',
        'text'  => '
            <p><strong>Exam Attendance</strong> records which students actually appeared in each exam session.</p>
            <ul>
                <li>Attendance is marked room-by-room for each session (AN/FN).</li>
                <li>Absent students are flagged — this feeds into result processing (an absent student gets 0 for external marks and fails that subject).</li>
                <li>QR-based quick marking is supported: invigilators scan student hall ticket QR codes.</li>
                <li>The attendance sheet is the basis for the official absentee report submitted to the university.</li>
            </ul>
            <p class="text-info"><i class="fa fa-lightbulb-o"></i> Mark attendance as soon as possible after each session. Late or missing attendance records will block result computation.</p>
        ',
    ],
    'ufm' => [
        'title' => 'UFM / Malpractice',
        'icon'  => 'fa-warning',
        'color' => '#c62828',
        'text'  => '
            <p><strong>UFM (Unfair Means / Malpractice)</strong> logs and tracks misconduct incidents during exams.</p>
            <ul>
                <li>Invigilators report incidents — student details, room, session, nature of malpractice (chit, mobile, copying, etc.).</li>
                <li>The CoE reviews the report and assigns status: <em>pending → under investigation → resolved</em>.</li>
                <li>A formal UFM report is generated for the Discipline Committee and university submission.</li>
                <li>Students with active UFM cases may have their results withheld until the case is resolved.</li>
            </ul>
            <p class="text-info"><i class="fa fa-lightbulb-o"></i> All UFM cases must be documented the same day as the incident. Delay weakens the evidentiary record for the Discipline Committee.</p>
        ',
    ],
    'answer_scripts' => [
        'title' => 'Answer Scripts Management',
        'icon'  => 'fa-file-text-o',
        'color' => '#37474f',
        'text'  => '
            <p><strong>Answer Scripts</strong> tracks the collection, bundling, dispatch, and receipt of student answer books after each exam session.</p>
            <ul>
                <li>Scripts are counted per room and bundled subject-wise after each session.</li>
                <li>Bundles are dispatched to examiners (internal/external) for valuation.</li>
                <li>Receipt by examiner is logged; return with marks is tracked.</li>
                <li>Provides an audit chain: from invigilator → bundle → examiner → marks entry.</li>
            </ul>
            <p class="text-info"><i class="fa fa-lightbulb-o"></i> Never dispatch scripts without a receipt log. This is the chain-of-custody record that the university may audit.</p>
        ',
    ],
    'osm' => [
        'title' => 'On-Screen Marking (OSM)',
        'icon'  => 'fa-pencil-square-o',
        'color' => '#4527a0',
        'text'  => '
            <p><strong>On-Screen Marking (OSM)</strong> is a digital valuation workflow where answer scripts are scanned and evaluated on-screen by examiners.</p>
            <ul>
                <li>Answer scripts are scanned and uploaded to the system after collection.</li>
                <li>Scripts are anonymised (register numbers masked) before assignment to evaluators.</li>
                <li>Evaluators mark questions individually on screen; marks are totalled automatically.</li>
                <li>Chief Examiner can review marked scripts and approve before marks are submitted to results.</li>
                <li>Supports double-valuation and moderation workflows.</li>
            </ul>
            <p class="text-info"><i class="fa fa-lightbulb-o"></i> OSM eliminates manual mark compilation errors. Ensure evaluators are trained and have reliable internet access before OSM sessions begin.</p>
        ',
    ],
    'revaluation' => [
        'title' => 'Revaluation',
        'icon'  => 'fa-refresh',
        'color' => '#1976d2',
        'text'  => '
            <p><strong>Revaluation</strong> handles student requests to re-evaluate their answer scripts after results are published.</p>
            <ul>
                <li>Students (or CoE on their behalf) submit a revaluation request with the required fee payment.</li>
                <li>Requests are validated and the original answer script is retrieved.</li>
                <li>A second examiner evaluates the script independently.</li>
                <li>If the re-evaluated mark differs from the original, the higher of the two is awarded and the result is updated.</li>
            </ul>
            <p class="text-info"><i class="fa fa-lightbulb-o"></i> Revaluation must be completed within the university-prescribed timeline (usually 30 days from result publication). Track pending requests carefully.</p>
        ',
    ],
    'moderation' => [
        'title' => 'Moderation / Grace Marks',
        'icon'  => 'fa-sliders',
        'color' => '#2e7d32',
        'text'  => '
            <p><strong>Moderation (Grace Marks)</strong> applies adjustments to raw exam scores before final result computation.</p>
            <ul>
                <li><strong>Flat marks</strong> — add a fixed number of marks to all students in a subject (e.g., +3 due to a tough paper).</li>
                <li><strong>Percentage boost</strong> — add a percentage of scored marks (e.g., +5% of external marks).</li>
                <li><strong>Grace to pass</strong> — add just enough marks to bring a student from near-fail to exactly 50.</li>
                <li>Moderation rules are configured, previewed (to see impact), and then applied. Applied rules cannot be reversed without a system override.</li>
            </ul>
            <p class="text-info"><i class="fa fa-lightbulb-o"></i> Always preview before applying. Moderation affects all downstream computations — SGPA, arrear count, results publication. Requires CoE/Principal approval.</p>
        ',
    ],
    'marks' => [
        'title' => 'Marks & Results Entry',
        'icon'  => 'fa-graduation-cap',
        'color' => '#1565c0',
        'text'  => '
            <p><strong>Marks &amp; Results</strong> is where external exam scores are entered and final results are computed.</p>
            <ul>
                <li><strong>Subject configuration</strong> — set max marks, passing marks, and credit value for each subject.</li>
                <li><strong>Marks entry</strong> — enter external (university) marks per student per subject. Internal marks are pulled from the existing system.</li>
                <li><strong>Grade computation</strong> — system computes letter grade (O/A+/A/B+/B/C/U) per Anna University NEP2020 scale and grade point.</li>
                <li><strong>SGPA</strong> — automatically calculated as (Σ grade_points × credits) ÷ total_credits.</li>
                <li>Arrear tracking — subjects where the student scored below 50 total are flagged as arrears.</li>
            </ul>
            <p class="text-info"><i class="fa fa-lightbulb-o"></i> Apply moderation (if any) <em>before</em> computing SGPA. Once published, a recompute is needed to reflect any mark corrections.</p>
        ',
    ],
    'results' => [
        'title' => 'Result Publication',
        'icon'  => 'fa-bullhorn',
        'color' => '#37474f',
        'text'  => '
            <p><strong>Result Publication</strong> controls when and how results are made visible to students and the institution.</p>
            <ul>
                <li><strong>Publish</strong> — makes results visible to students on the student portal. Generates the official result gazette PDF.</li>
                <li><strong>Withhold</strong> — results can be withheld for students with fee dues, active UFM cases, or pending revaluation.</li>
                <li><strong>Student Result Card</strong> — per-student transcript showing subject-wise marks, grades, SGPA, and arrear status.</li>
                <li><strong>Export</strong> — download results in CSV/PDF for submission to Anna University or statutory reporting.</li>
            </ul>
            <p class="text-info"><i class="fa fa-lightbulb-o"></i> Verify that all marks are entered, moderation applied, and SGPA computed before publishing. Publication is a public-facing action — students will see results immediately.</p>
        ',
    ],
];

$h = isset($helps[$help_key]) ? $helps[$help_key] : ['title' => 'Help', 'icon' => 'fa-info-circle', 'color' => '#1565c0', 'text' => '<p>No information available for this screen.</p>'];
?>

<style>
.coe-info-btn {
    background: none;
    border: none;
    padding: 0 0 0 8px;
    cursor: pointer;
    vertical-align: middle;
    line-height: 1;
    opacity: .75;
    transition: opacity .2s;
}
.coe-info-btn:hover { opacity: 1; }
.coe-info-btn .fa { font-size: 1.1rem; color: #1976d2; }
#coeHelpModal .modal-header {
    color: #fff;
    border-radius: 4px 4px 0 0;
}
#coeHelpModal .modal-body ul { padding-left: 20px; }
#coeHelpModal .modal-body ul li { margin-bottom: 6px; }
#coeHelpModal .modal-body p.text-info { background: #e3f2fd; border-left: 4px solid #1976d2; padding: 8px 12px; border-radius: 4px; color: #0d47a1 !important; margin-top: 12px; }
</style>

<!-- CoE Help Modal -->
<div class="modal fade" id="coeHelpModal" tabindex="-1" role="dialog" aria-labelledby="coeHelpModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:<?php echo $h['color']; ?>;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:.9;" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="coeHelpModalLabel">
                    <i class="fa <?php echo $h['icon']; ?>"></i>
                    &nbsp;<?php echo htmlspecialchars($h['title']); ?>
                    &nbsp;<small style="color:rgba(255,255,255,.75);font-size:.75em;">How does this work?</small>
                </h4>
            </div>
            <div class="modal-body" style="font-size:.95rem;line-height:1.7;">
                <?php echo $h['text']; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
            </div>
        </div>
    </div>
</div>
