import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import { surveyService } from '../services';
import { toast } from 'react-hot-toast';
import { ArrowLeft, Send, Star, CheckCircle } from 'lucide-react';

const Survey = () => {
  const { user } = useAuth();
  const navigate = useNavigate();
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [questions, setQuestions] = useState([]);
  const [teachers, setTeachers] = useState([]);
  const [responses, setResponses] = useState({});
  const [teacherRatings, setTeacherRatings] = useState({});
  const [completion, setCompletion] = useState(null);

  useEffect(() => {
    fetchSurveyData();
  }, []);

  const fetchSurveyData = async () => {
    try {
      const [questionsData, completionData] = await Promise.all([
        surveyService.getQuestions(),
        surveyService.getCompletionStatus()
      ]);
      
      setQuestions(questionsData.questions || []);
      setCompletion(completionData);

      // If student, fetch teachers for rating
      if (user?.role === 'student') {
        const teachersData = await surveyService.getTeachers();
        setTeachers(teachersData.teachers || []);
      }
    } catch (error) {
      console.error('Error fetching survey data:', error);
      toast.error('Failed to load survey data');
    } finally {
      setLoading(false);
    }
  };

  const handleResponseChange = (questionId, rating) => {
    setResponses(prev => ({
      ...prev,
      [questionId]: rating
    }));
  };

  const handleTeacherRating = (teacherId, rating) => {
    setTeacherRatings(prev => ({
      ...prev,
      [teacherId]: rating
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    // Validate responses
    const unansweredQuestions = questions.filter(q => !responses[q.id]);
    if (unansweredQuestions.length > 0) {
      toast.error('Please answer all questions before submitting');
      return;
    }

    setSubmitting(true);
    try {
      // Submit survey responses
      const responsesArray = Object.entries(responses).map(([questionId, rating]) => ({
        question_id: parseInt(questionId),
        rating: parseInt(rating)
      }));
      
      await surveyService.submitResponses({ responses: responsesArray });

      // Submit teacher ratings if student
      if (user?.role === 'student' && Object.keys(teacherRatings).length > 0) {
        // Submit each teacher rating individually
        for (const [teacherId, rating] of Object.entries(teacherRatings)) {
          await surveyService.submitTeacherRating({
            teacher_id: parseInt(teacherId),
            rating: parseInt(rating)
          });
        }
      }

      toast.success('Survey submitted successfully!');
      navigate('/dashboard');
    } catch (error) {
      console.error('Error submitting survey:', error);
      toast.error(error.response?.data?.error || 'Failed to submit survey');
    } finally {
      setSubmitting(false);
    }
  };

  const StarRating = ({ value, onChange, disabled = false, id }) => {
    return (
      <div className="flex gap-1">
        {[1, 2, 3, 4, 5].map((star) => (
          <button
            key={`${id}-star-${star}`}
            type="button"
            onClick={() => !disabled && onChange(star)}
            disabled={disabled}
            className={`p-1 transition-colors ${
              disabled ? 'cursor-not-allowed' : 'cursor-pointer hover:scale-110'
            }`}
          >
            <Star
              className={`w-8 h-8 ${
                star <= value
                  ? 'fill-yellow-400 text-yellow-400'
                  : 'text-gray-300'
              }`}
            />
          </button>
        ))}
      </div>
    );
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  if (completion?.isCompleted) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="bg-white p-8 rounded-lg shadow-md max-w-md w-full text-center">
          <CheckCircle className="w-16 h-16 text-green-500 mx-auto mb-4" />
          <h2 className="text-2xl font-bold text-gray-900 mb-2">Survey Completed!</h2>
          <p className="text-gray-600 mb-6">
            You have already completed the survey. Thank you for your feedback!
          </p>
          <button
            onClick={() => navigate('/dashboard')}
            className="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700"
          >
            <ArrowLeft className="w-5 h-5" />
            Back to Dashboard
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-white shadow-sm">
        <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
          <button
            onClick={() => navigate('/dashboard')}
            className="flex items-center gap-2 text-gray-600 hover:text-gray-900 mb-2"
          >
            <ArrowLeft className="w-5 h-5" />
            Back to Dashboard
          </button>
          <h1 className="text-2xl font-bold text-gray-900">Survey Form</h1>
          <p className="text-sm text-gray-600">
            Please rate each question on a scale of 1-5 stars
          </p>
        </div>
      </header>

      <main className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Progress Bar */}
        <div className="mb-8">
          <div className="flex justify-between text-sm text-gray-600 mb-2">
            <span>Progress</span>
            <span>
              {Object.keys(responses).length} / {questions.length} questions
            </span>
          </div>
          <div className="w-full bg-gray-200 rounded-full h-2">
            <div
              className="bg-blue-600 h-2 rounded-full transition-all duration-300"
              style={{
                width: `${(Object.keys(responses).length / questions.length) * 100}%`
              }}
            />
          </div>
        </div>

        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Survey Questions */}
          <div className="bg-white rounded-lg shadow-sm p-6">
            <h2 className="text-xl font-semibold text-gray-900 mb-6">Survey Questions</h2>
            <div className="space-y-8">
              {questions.map((question, index) => (
                <div key={question.id} className="border-b border-gray-200 pb-6 last:border-0">
                  <div className="flex items-start gap-4">
                    <span className="flex-shrink-0 w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-semibold">
                      {index + 1}
                    </span>
                    <div className="flex-1">
                      <p className="text-gray-900 mb-4">{question.question || question.question_text}</p>
                      <StarRating
                        id={`question-${question.id}`}
                        value={responses[question.id] || 0}
                        onChange={(rating) => handleResponseChange(question.id, rating)}
                      />
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Teacher Ratings (Students Only) */}
          {user?.role === 'student' && teachers.length > 0 && (
            <div className="bg-white rounded-lg shadow-sm p-6">
              <h2 className="text-xl font-semibold text-gray-900 mb-6">Rate Teachers</h2>
              <div className="space-y-6">
                {teachers.map((teacher) => (
                  <div key={teacher.id} className="flex items-center justify-between border-b border-gray-200 pb-4 last:border-0">
                    <div>
                      <p className="font-medium text-gray-900">{teacher.name || teacher.username}</p>
                      <p className="text-sm text-gray-600">{teacher.department}</p>
                    </div>
                    <StarRating
                      id={`teacher-${teacher.id}`}
                      value={teacherRatings[teacher.id] || 0}
                      onChange={(rating) => handleTeacherRating(teacher.id, rating)}
                    />
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Submit Button */}
          <div className="flex justify-end gap-4">
            <button
              type="button"
              onClick={() => navigate('/dashboard')}
              className="px-6 py-3 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={submitting || Object.keys(responses).length !== questions.length}
              className="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {submitting ? (
                <>
                  <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                  Submitting...
                </>
              ) : (
                <>
                  <Send className="w-5 h-5" />
                  Submit Survey
                </>
              )}
            </button>
          </div>
        </form>
      </main>
    </div>
  );
};

export default Survey;
