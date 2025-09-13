#!/usr/bin/env python3
"""
Advanced AI Analytics Engine for Educational Insights
Provides sophisticated sentiment analysis, trend prediction, and pattern recognition
"""

import json
import re
import sys
import math
from datetime import datetime, timedelta
from collections import defaultdict, Counter
import statistics

class AdvancedAIEngine:
    def __init__(self, config_path='ai_config.json'):
        """Initialize the AI engine with configuration"""
        try:
            with open(config_path, 'r', encoding='utf-8') as f:
                self.config = json.load(f)
        except FileNotFoundError:
            # Fallback configuration if file not found
            self.config = self._get_default_config()
        
        self.sentiment_cache = {}
        
    def _get_default_config(self):
        """Fallback configuration"""
        return {
            "sentiment_analysis": {
                "positive_patterns": [{"words": ["good", "great", "excellent"], "weight": 2}],
                "negative_patterns": [{"words": ["bad", "terrible", "awful"], "weight": 2}]
            }
        }
    
    def advanced_sentiment_analysis(self, text_data):
        """
        Advanced sentiment analysis with context awareness and pattern matching
        """
        results = {
            'overall_sentiment': 'neutral',
            'confidence': 0.0,
            'emotional_breakdown': {},
            'key_phrases': [],
            'context_analysis': {}
        }
        
        if not text_data:
            return results
        
        # Combine all texts for analysis
        combined_text = ' '.join(text_data).lower()
        
        # Sentiment scoring
        sentiment_scores = {'positive': 0, 'negative': 0, 'neutral': 0}
        emotional_indicators = defaultdict(int)
        key_phrases = []
        
        # Advanced pattern matching
        for pattern_group in self.config['sentiment_analysis']['positive_patterns']:
            weight = pattern_group.get('weight', 1)
            
            # Word-based analysis
            if 'words' in pattern_group:
                for word in pattern_group['words']:
                    count = combined_text.count(word.lower())
                    sentiment_scores['positive'] += count * weight
                    if count > 0:
                        emotional_indicators[f'positive_{word}'] = count
            
            # Phrase-based analysis
            if 'phrases' in pattern_group:
                for phrase in pattern_group['phrases']:
                    if phrase.lower() in combined_text:
                        sentiment_scores['positive'] += weight
                        key_phrases.append(phrase)
            
            # Context pattern analysis using regex
            if 'context_patterns' in pattern_group:
                for pattern in pattern_group['context_patterns']:
                    matches = re.findall(pattern, combined_text, re.IGNORECASE)
                    sentiment_scores['positive'] += len(matches) * weight
                    key_phrases.extend(matches)
        
        # Similar analysis for negative patterns
        for pattern_group in self.config['sentiment_analysis']['negative_patterns']:
            weight = pattern_group.get('weight', 1)
            
            if 'words' in pattern_group:
                for word in pattern_group['words']:
                    count = combined_text.count(word.lower())
                    sentiment_scores['negative'] += count * weight
                    if count > 0:
                        emotional_indicators[f'negative_{word}'] = count
            
            if 'phrases' in pattern_group:
                for phrase in pattern_group['phrases']:
                    if phrase.lower() in combined_text:
                        sentiment_scores['negative'] += weight
                        key_phrases.append(phrase)
            
            if 'context_patterns' in pattern_group:
                for pattern in pattern_group['context_patterns']:
                    matches = re.findall(pattern, combined_text, re.IGNORECASE)
                    sentiment_scores['negative'] += len(matches) * weight
                    key_phrases.extend(matches)
        
        # Handle negation and intensifiers
        sentiment_scores = self._apply_linguistic_modifiers(combined_text, sentiment_scores)
        
        # Calculate overall sentiment
        total_score = sum(sentiment_scores.values())
        if total_score > 0:
            pos_ratio = sentiment_scores['positive'] / total_score
            neg_ratio = sentiment_scores['negative'] / total_score
            
            if pos_ratio > 0.6:
                results['overall_sentiment'] = 'positive'
                results['confidence'] = min(pos_ratio * 100, 95)
            elif neg_ratio > 0.6:
                results['overall_sentiment'] = 'negative'
                results['confidence'] = min(neg_ratio * 100, 95)
            else:
                results['overall_sentiment'] = 'neutral'
                results['confidence'] = 50
        
        results['emotional_breakdown'] = dict(emotional_indicators)
        results['key_phrases'] = list(set(key_phrases))
        results['sentiment_scores'] = sentiment_scores
        
        return results
    
    def _apply_linguistic_modifiers(self, text, sentiment_scores):
        """Apply negation and intensifier rules"""
        words = text.split()
        modified_scores = sentiment_scores.copy()
        
        negation_words = self.config['sentiment_analysis'].get('negation_words', [])
        intensifiers = self.config['sentiment_analysis'].get('intensifiers', [])
        
        for i, word in enumerate(words):
            # Check for negation context (within 3 words)
            negation_context = False
            for j in range(max(0, i-3), i):
                if words[j] in negation_words:
                    negation_context = True
                    break
            
            # Check for intensifiers
            intensifier_multiplier = 1.0
            for j in range(max(0, i-2), i):
                if words[j] in intensifiers:
                    intensifier_multiplier = 1.5
                    break
            
            # Apply modifiers to sentiment scores
            if negation_context:
                # Flip sentiment for negated words
                if sentiment_scores['positive'] > 0:
                    modified_scores['negative'] += sentiment_scores['positive'] * 0.8
                    modified_scores['positive'] *= 0.2
                elif sentiment_scores['negative'] > 0:
                    modified_scores['positive'] += sentiment_scores['negative'] * 0.8
                    modified_scores['negative'] *= 0.2
            
            if intensifier_multiplier > 1.0:
                modified_scores['positive'] *= intensifier_multiplier
                modified_scores['negative'] *= intensifier_multiplier
        
        return modified_scores
    
    def predictive_analysis(self, historical_data):
        """
        Advanced predictive analysis for future trends
        """
        predictions = {
            'trend_direction': 'stable',
            'confidence': 0,
            'forecasted_metrics': {},
            'risk_factors': [],
            'opportunities': [],
            'recommendations': []
        }
        
        if len(historical_data) < 5:
            predictions['confidence'] = 10
            predictions['recommendations'].append("Need more historical data for accurate predictions")
            return predictions
        
        # Time series analysis
        dates = [item['date'] for item in historical_data]
        ratings = [float(item['rating']) for item in historical_data]
        
        # Calculate trend using linear regression
        trend_slope = self._calculate_trend_slope(ratings)
        
        # Predict future values
        future_rating = self._predict_future_rating(ratings, trend_slope)
        
        # Seasonal pattern detection
        seasonal_patterns = self._detect_seasonal_patterns(historical_data)
        
        # Risk assessment
        volatility = statistics.stdev(ratings) if len(ratings) > 1 else 0
        
        # Generate predictions
        if trend_slope > 0.1:
            predictions['trend_direction'] = 'improving'
            predictions['confidence'] = min(abs(trend_slope) * 50, 85)
        elif trend_slope < -0.1:
            predictions['trend_direction'] = 'declining'
            predictions['confidence'] = min(abs(trend_slope) * 50, 85)
        else:
            predictions['trend_direction'] = 'stable'
            predictions['confidence'] = 60
        
        predictions['forecasted_metrics'] = {
            'next_month_rating': round(future_rating, 2),
            'trend_slope': round(trend_slope, 4),
            'volatility': round(volatility, 2)
        }
        
        # Generate risk factors and opportunities
        if volatility > 1.0:
            predictions['risk_factors'].append(f"High volatility detected ({volatility:.2f})")
        
        if trend_slope < -0.2:
            predictions['risk_factors'].append("Significant declining trend")
        elif trend_slope > 0.2:
            predictions['opportunities'].append("Strong positive trend")
        
        # Generate recommendations
        predictions['recommendations'] = self._generate_predictive_recommendations(
            trend_slope, volatility, seasonal_patterns
        )
        
        return predictions
    
    def _calculate_trend_slope(self, values):
        """Calculate trend slope using least squares method"""
        n = len(values)
        x = list(range(n))
        
        sum_x = sum(x)
        sum_y = sum(values)
        sum_xy = sum(x[i] * values[i] for i in range(n))
        sum_x2 = sum(x[i] ** 2 for i in range(n))
        
        slope = (n * sum_xy - sum_x * sum_y) / (n * sum_x2 - sum_x ** 2)
        return slope
    
    def _predict_future_rating(self, historical_ratings, trend_slope):
        """Predict future rating based on trend"""
        current_avg = statistics.mean(historical_ratings[-3:])  # Last 3 values
        periods_ahead = 1  # Predict 1 period ahead
        
        return current_avg + (trend_slope * periods_ahead)
    
    def _detect_seasonal_patterns(self, data):
        """Detect seasonal patterns in the data"""
        # Simple seasonal detection - can be enhanced with more sophisticated algorithms
        monthly_patterns = defaultdict(list)
        
        for item in data:
            try:
                date_obj = datetime.strptime(item['date'], '%Y-%m-%d')
                month = date_obj.month
                monthly_patterns[month].append(float(item['rating']))
            except (ValueError, KeyError):
                continue
        
        seasonal_trends = {}
        for month, ratings in monthly_patterns.items():
            if len(ratings) > 1:
                seasonal_trends[f"month_{month}"] = statistics.mean(ratings)
        
        return seasonal_trends
    
    def _generate_predictive_recommendations(self, trend_slope, volatility, seasonal_patterns):
        """Generate predictive recommendations"""
        recommendations = []
        
        if trend_slope < -0.1:
            recommendations.append("Implement immediate intervention strategies")
            recommendations.append("Increase monitoring frequency")
        
        if volatility > 0.8:
            recommendations.append("Focus on consistency improvements")
            recommendations.append("Identify and address variable factors")
        
        if trend_slope > 0.1:
            recommendations.append("Maintain current successful practices")
            recommendations.append("Consider scaling successful initiatives")
        
        if seasonal_patterns:
            recommendations.append("Prepare for seasonal variations based on historical patterns")
        
        return recommendations
    
    def enhanced_topic_extraction(self, texts):
        """Enhanced topic extraction with category classification"""
        topic_analysis = {
            'primary_topics': {},
            'category_breakdown': {},
            'emerging_themes': [],
            'priority_issues': []
        }
        
        combined_text = ' '.join(texts).lower()
        
        # Extract topics by category
        education_topics = self.config['topic_extraction']['education_topics']
        
        for category, keywords in education_topics.items():
            category_score = 0
            found_keywords = []
            
            for keyword in keywords:
                count = combined_text.count(keyword.lower())
                category_score += count
                if count > 0:
                    found_keywords.append({'keyword': keyword, 'count': count})
            
            if category_score > 0:
                topic_analysis['category_breakdown'][category] = {
                    'score': category_score,
                    'keywords': found_keywords
                }
        
        # Priority keyword analysis
        priority_keywords = self.config['topic_extraction'].get('priority_keywords', [])
        for priority_item in priority_keywords:
            keyword = priority_item['keyword']
            if keyword.lower() in combined_text:
                topic_analysis['priority_issues'].append({
                    'keyword': keyword,
                    'category': priority_item['category'],
                    'priority': priority_item['priority'],
                    'frequency': combined_text.count(keyword.lower())
                })
        
        # Sort by priority
        topic_analysis['priority_issues'].sort(key=lambda x: x['priority'], reverse=True)
        
        return topic_analysis

def main():
    """Main function for command-line usage"""
    if len(sys.argv) < 3:
        print("Usage: python ai_engine.py <function> <data_json>")
        return
    
    function_name = sys.argv[1]
    data_json = sys.argv[2]
    
    try:
        data = json.loads(data_json)
        engine = AdvancedAIEngine()
        
        if function_name == 'sentiment_analysis':
            result = engine.advanced_sentiment_analysis(data.get('texts', []))
        elif function_name == 'predictive_analysis':
            result = engine.predictive_analysis(data.get('historical_data', []))
        elif function_name == 'topic_extraction':
            result = engine.enhanced_topic_extraction(data.get('texts', []))
        else:
            result = {'error': 'Unknown function'}
        
        print(json.dumps(result))
        
    except Exception as e:
        print(json.dumps({'error': str(e)}))

if __name__ == '__main__':
    main()
