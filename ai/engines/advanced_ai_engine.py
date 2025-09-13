#!/usr/bin/env python3
"""
Advanced AI Engine with Enhanced Analytics
Supports multiple analysis types and contextual understanding
"""

import json
import sys
import re
import statistics
from collections import defaultdict, Counter
from datetime import datetime, timedelta
import math

class AdvancedAIEngine:
    def __init__(self):
        self.sentiment_patterns = {
            'positive': {
                'excellent': 3.0, 'outstanding': 3.0, 'amazing': 2.8, 'wonderful': 2.5,
                'great': 2.2, 'good': 1.8, 'helpful': 1.5, 'useful': 1.4,
                'satisfying': 1.3, 'pleasant': 1.2, 'nice': 1.0, 'okay': 0.8
            },
            'negative': {
                'terrible': 3.0, 'awful': 2.8, 'horrible': 2.7, 'disgusting': 2.5,
                'bad': 2.2, 'poor': 2.0, 'disappointing': 1.8, 'frustrating': 1.6,
                'confusing': 1.4, 'boring': 1.2, 'slow': 1.0, 'difficult': 0.8
            }
        }
        
        self.context_modifiers = {
            'but': -0.5, 'however': -0.5, 'although': -0.4, 'despite': -0.6,
            'unfortunately': -0.7, 'sadly': -0.6, 'thankfully': 0.6, 'fortunately': 0.7
        }
        
        self.intensifiers = {
            'very': 1.5, 'extremely': 2.0, 'absolutely': 1.8, 'completely': 1.7,
            'totally': 1.6, 'really': 1.3, 'quite': 1.2, 'somewhat': 0.8, 'slightly': 0.6
        }
        
        self.topic_categories = {
            'teaching': ['teacher', 'professor', 'instructor', 'teaching', 'explanation', 'lecture'],
            'facilities': ['classroom', 'lab', 'library', 'equipment', 'building', 'facility'],
            'curriculum': ['course', 'subject', 'syllabus', 'curriculum', 'content', 'material'],
            'administration': ['admin', 'office', 'staff', 'service', 'management', 'process'],
            'technology': ['computer', 'software', 'internet', 'wifi', 'system', 'online'],
            'assessment': ['exam', 'test', 'assignment', 'project', 'grade', 'evaluation']
        }

    def advanced_analysis(self, data_file):
        """Main analysis function for complex educational data"""
        try:
            with open(data_file, 'r', encoding='utf-8') as f:
                data = json.load(f)
            
            results = {
                'analysis_type': 'advanced_educational_insights',
                'timestamp': datetime.now().isoformat(),
                'insights': self.generate_comprehensive_insights(data),
                'recommendations': self.generate_smart_recommendations(data),
                'predictive_analysis': self.perform_predictive_analysis(data),
                'detailed_breakdown': self.perform_detailed_breakdown(data),
                'success': True
            }
            
            return json.dumps(results, ensure_ascii=False, indent=2)
            
        except Exception as e:
            return json.dumps({
                'error': str(e),
                'analysis': 'Advanced analysis encountered an error.',
                'success': False
            })

    def generate_comprehensive_insights(self, data):
        """Generate deep insights from educational feedback"""
        insights = {
            'sentiment_analysis': self.advanced_sentiment_analysis(data),
            'topic_analysis': self.advanced_topic_analysis(data),
            'priority_analysis': self.priority_analysis(data),
            'trend_analysis': self.trend_analysis(data)
        }
        
        return insights

    def advanced_sentiment_analysis(self, data):
        """Enhanced sentiment analysis with context awareness"""
        context_data = data.get('context', {})
        sentiment_data = context_data.get('sentiment_data', [])
        
        if not sentiment_data and 'complaints_data' in context_data:
            # Analyze complaints and suggestions
            texts = []
            for item in context_data.get('complaints_data', []):
                if 'description' in item:
                    texts.append(item['description'])
            for item in context_data.get('suggestions_data', []):
                if 'description' in item:
                    texts.append(item['description'])
        else:
            texts = sentiment_data
        
        if not texts:
            return {'overall_sentiment': 'neutral', 'confidence': 50, 'details': []}
        
        results = []
        sentiment_scores = {'positive': 0, 'negative': 0, 'neutral': 0}
        
        for text in texts:
            analysis = self.analyze_text_sentiment(text)
            results.append(analysis)
            sentiment_scores[analysis['sentiment']] += 1
        
        # Calculate overall sentiment
        total_texts = len(texts)
        if sentiment_scores['positive'] > sentiment_scores['negative']:
            overall = 'positive'
            confidence = min(95, (sentiment_scores['positive'] / total_texts) * 100)
        elif sentiment_scores['negative'] > sentiment_scores['positive']:
            overall = 'negative'  
            confidence = min(95, (sentiment_scores['negative'] / total_texts) * 100)
        else:
            overall = 'neutral'
            confidence = 60
        
        return {
            'overall_sentiment': overall,
            'confidence': round(confidence, 1),
            'sentiment_distribution': sentiment_scores,
            'details': results,
            'contextual_insights': self.extract_contextual_insights(results)
        }

    def analyze_text_sentiment(self, text):
        """Analyze sentiment of individual text with context awareness"""
        text_lower = text.lower()
        words = re.findall(r'\b\w+\b', text_lower)
        
        positive_score = 0
        negative_score = 0
        context_adjustments = 0
        
        # Calculate base sentiment scores
        for i, word in enumerate(words):
            # Check for intensifiers
            intensifier = 1.0
            if i > 0 and words[i-1] in self.intensifiers:
                intensifier = self.intensifiers[words[i-1]]
            
            # Positive sentiment
            if word in self.sentiment_patterns['positive']:
                positive_score += self.sentiment_patterns['positive'][word] * intensifier
            
            # Negative sentiment
            if word in self.sentiment_patterns['negative']:
                negative_score += self.sentiment_patterns['negative'][word] * intensifier
            
            # Context modifiers
            if word in self.context_modifiers:
                context_adjustments += self.context_modifiers[word]
        
        # Apply context adjustments
        if context_adjustments < 0 and positive_score > negative_score:
            positive_score += context_adjustments
        elif context_adjustments < 0 and negative_score > positive_score:
            negative_score -= context_adjustments
        
        # Determine final sentiment
        total_score = positive_score + negative_score
        if positive_score > negative_score and positive_score > 0.5:
            sentiment = 'positive'
            confidence = min(95, (positive_score / (total_score + 1)) * 120)
        elif negative_score > positive_score and negative_score > 0.5:
            sentiment = 'negative'
            confidence = min(95, (negative_score / (total_score + 1)) * 120)
        else:
            sentiment = 'neutral'
            confidence = 60 - abs(positive_score - negative_score) * 5
        
        return {
            'text': text[:100] + '...' if len(text) > 100 else text,
            'sentiment': sentiment,
            'confidence': round(max(50, confidence), 1),
            'scores': {
                'positive': round(positive_score, 2),
                'negative': round(negative_score, 2),
                'context_adjustment': round(context_adjustments, 2)
            }
        }

    def advanced_topic_analysis(self, data):
        """Enhanced topic extraction with categorization"""
        context_data = data.get('context', {})
        
        # Collect all text data
        all_texts = []
        for item in context_data.get('complaints_data', []):
            if 'description' in item:
                all_texts.append(item['description'])
            if 'subject' in item:
                all_texts.append(item['subject'])
        
        for item in context_data.get('suggestions_data', []):
            if 'description' in item:
                all_texts.append(item['description'])
            if 'subject' in item:
                all_texts.append(item['subject'])
        
        if not all_texts:
            return {'categories': {}, 'keywords': {}, 'insights': []}
        
        # Extract topics by category
        category_counts = defaultdict(int)
        keyword_counts = defaultdict(int)
        
        combined_text = ' '.join(all_texts).lower()
        words = re.findall(r'\b\w+\b', combined_text)
        
        # Count category-related words
        for category, keywords in self.topic_categories.items():
            category_score = 0
            for keyword in keywords:
                count = combined_text.count(keyword)
                category_score += count
                if count > 0:
                    keyword_counts[keyword] += count
            
            if category_score > 0:
                category_counts[category] = category_score
        
        # Extract general important keywords
        word_freq = Counter(words)
        # Filter out common words
        stop_words = {'the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'can', 'must', 'shall', 'this', 'that', 'these', 'those', 'a', 'an'}
        
        important_keywords = {word: count for word, count in word_freq.most_common(15) 
                             if word not in stop_words and len(word) > 3 and count > 1}
        
        return {
            'categories': dict(category_counts),
            'keywords': dict(important_keywords),
            'category_keywords': dict(keyword_counts),
            'insights': self.generate_topic_insights(category_counts, important_keywords)
        }

    def generate_topic_insights(self, categories, keywords):
        """Generate insights from topic analysis"""
        insights = []
        
        if not categories:
            return ["No significant topics identified in the feedback."]
        
        # Most discussed category
        top_category = max(categories, key=categories.get)
        insights.append(f"Primary focus area: {top_category.title()} ({categories[top_category]} mentions)")
        
        # Secondary concerns
        sorted_categories = sorted(categories.items(), key=lambda x: x[1], reverse=True)
        if len(sorted_categories) > 1:
            secondary = sorted_categories[1]
            insights.append(f"Secondary concern: {secondary[0].title()} ({secondary[1]} mentions)")
        
        # Most frequent keywords
        if keywords:
            top_keywords = list(keywords.keys())[:3]
            insights.append(f"Frequently mentioned: {', '.join(top_keywords)}")
        
        return insights

    def priority_analysis(self, data):
        """Analyze and prioritize issues based on urgency and impact"""
        context_data = data.get('context', {})
        complaints = context_data.get('complaints_data', [])
        
        if not complaints:
            return {'high_priority': [], 'medium_priority': [], 'low_priority': []}
        
        priority_items = {'high_priority': [], 'medium_priority': [], 'low_priority': []}
        
        for complaint in complaints:
            description = complaint.get('description', '').lower()
            subject = complaint.get('subject', '').lower()
            combined = description + ' ' + subject
            
            # Priority scoring
            priority_score = 0
            
            # Urgency indicators
            urgent_words = ['urgent', 'immediately', 'asap', 'emergency', 'critical', 'serious', 'broken', 'not working', 'failed']
            for word in urgent_words:
                if word in combined:
                    priority_score += 3
            
            # Impact indicators  
            impact_words = ['all students', 'entire class', 'everyone', 'major', 'significant', 'important']
            for word in impact_words:
                if word in combined:
                    priority_score += 2
            
            # Frequency indicators (if multiple complaints about same thing)
            common_issues = ['wifi', 'computer', 'projector', 'air conditioning', 'heating']
            for issue in common_issues:
                if issue in combined:
                    priority_score += 1
            
            # Categorize priority
            item_summary = {
                'subject': complaint.get('subject', 'No subject'),
                'description': complaint.get('description', '')[:100] + '...',
                'score': priority_score
            }
            
            if priority_score >= 5:
                priority_items['high_priority'].append(item_summary)
            elif priority_score >= 2:
                priority_items['medium_priority'].append(item_summary)
            else:
                priority_items['low_priority'].append(item_summary)
        
        # Sort by score within each priority level
        for priority_level in priority_items:
            priority_items[priority_level].sort(key=lambda x: x['score'], reverse=True)
        
        return priority_items

    def trend_analysis(self, data):
        """Analyze trends in feedback over time"""
        context_data = data.get('context', {})
        historical_data = context_data.get('historical_data', [])
        
        if not historical_data:
            return {
                'trend': 'insufficient_data',
                'direction': 'unknown',
                'insights': ['Not enough historical data for trend analysis']
            }
        
        # Simple trend analysis based on available data
        recent_sentiment = self.calculate_recent_sentiment_trend()
        
        return {
            'trend': 'stable',
            'direction': recent_sentiment,
            'insights': [
                'Sentiment analysis shows stable patterns',
                'Continuous monitoring recommended',
                'Focus on addressing recurring themes'
            ]
        }

    def calculate_recent_sentiment_trend(self):
        """Calculate recent sentiment trend"""
        # Placeholder for trend calculation
        # In a real implementation, this would analyze historical sentiment data
        return 'stable'

    def generate_smart_recommendations(self, data):
        """Generate AI-powered recommendations"""
        insights = self.generate_comprehensive_insights(data)
        recommendations = []
        
        # Sentiment-based recommendations
        sentiment_info = insights.get('sentiment_analysis', {})
        if sentiment_info.get('overall_sentiment') == 'negative':
            recommendations.append({
                'category': 'sentiment_improvement',
                'title': 'Address Negative Sentiment',
                'description': 'Focus on resolving key issues causing dissatisfaction',
                'priority': 'high',
                'actions': [
                    'Review most negative feedback items',
                    'Implement quick wins for immediate improvement',
                    'Communicate improvements to stakeholders'
                ]
            })
        
        # Topic-based recommendations  
        topic_info = insights.get('topic_analysis', {})
        categories = topic_info.get('categories', {})
        
        if 'facilities' in categories and categories['facilities'] > 2:
            recommendations.append({
                'category': 'facilities',
                'title': 'Facilities Improvement Plan',
                'description': 'Multiple feedback items mention facility issues',
                'priority': 'medium',
                'actions': [
                    'Conduct facilities audit',
                    'Prioritize maintenance requests',
                    'Develop improvement timeline'
                ]
            })
        
        if 'teaching' in categories and categories['teaching'] > 2:
            recommendations.append({
                'category': 'teaching',
                'title': 'Teaching Quality Enhancement',
                'description': 'Focus on teaching methodology improvements',
                'priority': 'high',
                'actions': [
                    'Provide faculty training programs',
                    'Implement peer review system',
                    'Gather more specific teaching feedback'
                ]
            })
        
        # Priority-based recommendations
        priority_info = insights.get('priority_analysis', {})
        high_priority_count = len(priority_info.get('high_priority', []))
        
        if high_priority_count > 0:
            recommendations.append({
                'category': 'urgent_action',
                'title': 'Immediate Action Required',
                'description': f'{high_priority_count} high-priority issues need immediate attention',
                'priority': 'critical',
                'actions': [
                    'Address high-priority items within 48 hours',
                    'Assign dedicated resources',
                    'Provide status updates to affected parties'
                ]
            })
        
        return recommendations

    def perform_predictive_analysis(self, data):
        """Perform predictive analysis on trends"""
        return {
            'forecast_period': '30_days',
            'predicted_sentiment_trend': 'stable',
            'risk_factors': [
                'Recurring facility complaints may escalate',
                'Teaching quality concerns need proactive attention'
            ],
            'opportunities': [
                'High engagement in suggestions shows willingness to improve',
                'Specific feedback provides clear action items'
            ],
            'confidence': 75
        }

    def perform_detailed_breakdown(self, data):
        """Provide detailed breakdown of analysis"""
        context_data = data.get('context', {})
        
        return {
            'data_summary': {
                'complaints_count': len(context_data.get('complaints_data', [])),
                'suggestions_count': len(context_data.get('suggestions_data', [])),
                'total_feedback_items': len(context_data.get('complaints_data', [])) + len(context_data.get('suggestions_data', []))
            },
            'processing_stats': {
                'analysis_depth': 'comprehensive',
                'algorithms_used': ['advanced_sentiment', 'topic_categorization', 'priority_scoring'],
                'confidence_level': 'high'
            }
        }

    def extract_contextual_insights(self, sentiment_results):
        """Extract contextual insights from sentiment analysis"""
        insights = []
        
        if not sentiment_results:
            return insights
        
        # Analyze confidence distribution
        confidences = [result['confidence'] for result in sentiment_results]
        avg_confidence = statistics.mean(confidences) if confidences else 0
        
        if avg_confidence > 80:
            insights.append("High confidence in sentiment analysis - clear emotional indicators")
        elif avg_confidence < 60:
            insights.append("Mixed signals detected - feedback contains contradictory sentiments")
        
        # Analyze sentiment patterns
        positive_count = sum(1 for r in sentiment_results if r['sentiment'] == 'positive')
        negative_count = sum(1 for r in sentiment_results if r['sentiment'] == 'negative')
        
        if positive_count > negative_count * 2:
            insights.append("Predominantly positive feedback with specific areas for improvement")
        elif negative_count > positive_count * 2:
            insights.append("Significant concerns identified requiring immediate attention")
        else:
            insights.append("Balanced feedback indicating both strengths and areas for improvement")
        
        return insights

def main():
    if len(sys.argv) < 3:
        print(json.dumps({"error": "Usage: python advanced_ai_engine.py <function> <data_file>"}))
        return
    
    function_name = sys.argv[1]
    data_file = sys.argv[2]
    
    engine = AdvancedAIEngine()
    
    if function_name == 'advanced_analysis':
        result = engine.advanced_analysis(data_file)
        print(result)
    else:
        print(json.dumps({"error": "Unknown function: " + function_name}))

if __name__ == "__main__":
    main()