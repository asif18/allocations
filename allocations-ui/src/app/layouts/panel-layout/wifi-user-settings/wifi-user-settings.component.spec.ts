import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { SelfOtpSettingsComponent } from './self-otp-settings.component';

describe('SelfOtpSettingsComponent', () => {
  let component: SelfOtpSettingsComponent;
  let fixture: ComponentFixture<SelfOtpSettingsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ SelfOtpSettingsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SelfOtpSettingsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
